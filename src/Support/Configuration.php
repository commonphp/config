<?php /** @noinspection PhpUnused */

namespace CommonPHP\Configuration\Support;

use CommonPHP\Configuration\ConfigurationManager;
use CommonPHP\Configuration\Contracts\ConfigurationDriverContract;
use CommonPHP\Configuration\Exceptions\AccessDeniedException;
use CommonPHP\Configuration\Exceptions\ConfigurationLoadFailedException;
use CommonPHP\Configuration\Exceptions\ConfigurationSaveFailedException;
use CommonPHP\Configuration\Exceptions\ParentAccessDeniedException;
use CommonPHP\Configuration\Exceptions\ParentDirectoryMissingException;
use CommonPHP\Configuration\Exceptions\SaveNotSupportedException;
use Throwable;

final class Configuration
{
    /** @var string The absolute path to the configuration file. */
    public readonly string $filename;

    /** @var array The loaded configuration data. */
    public array $data = [];

    /** @var ConfigurationDriverContract The driver responsible for loading and saving this configuration. */
    private ConfigurationDriverContract $driver;

    /** @var ConfigurationManager The ConfigurationManager instance. */
    private ConfigurationManager $configurationManager;

    /**
     * @throws AccessDeniedException
     * @throws ConfigurationLoadFailedException
     */
    public function __construct(ConfigurationManager $configurationManager, ConfigurationDriverContract $driver, string $absolutePath)
    {
        $this->configurationManager = $configurationManager;
        $this->driver = $driver;
        $this->filename = $absolutePath;
        $this->reload();
    }

    /**
     * @throws AccessDeniedException
     * @throws ConfigurationLoadFailedException
     */
    public function reload(): void
    {
        $this->data = [];
        if (!file_exists($this->filename)) return;
        if (!is_readable($this->filename))
        {
            throw new AccessDeniedException($this->filename, 'read');
        }
        try {
            $this->data = $this->driver->load($this->filename);
        } catch (Throwable $t) {
            throw new ConfigurationLoadFailedException(get_class($this->driver), $t);
        }
    }

    public function canSave(): bool
    {
        return $this->driver->canSave();
    }

    /**
     * @throws ParentAccessDeniedException
     * @throws ParentDirectoryMissingException
     * @throws SaveNotSupportedException
     * @throws ConfigurationSaveFailedException
     * @throws AccessDeniedException
     */
    public function save(): void
    {
        if (!$this->driver->canSave())
        {
            switch ($this->configurationManager->configurationCannotSaveBehavior)
            {
                case ConfigurationCannotSaveBehavior::THROW:
                    throw new SaveNotSupportedException(get_class($this->driver));
                case ConfigurationCannotSaveBehavior::WARN:
                    trigger_error((new SaveNotSupportedException(get_class($this->driver)))->getMessage(), E_USER_WARNING);
                    break;
                case ConfigurationCannotSaveBehavior::NOTICE:
                    trigger_error((new SaveNotSupportedException(get_class($this->driver)))->getMessage());
                    break;
                default: break; // Do nothing
            }
        }
        else
        {
            $parent = dirname($this->filename);
            if (!is_dir($parent))
            {
                if (!mkdir($parent, 0755, true))
                {
                    throw new ParentDirectoryMissingException($this->filename);
                }
            }
            if (!is_writable($parent))
            {
                throw new ParentAccessDeniedException($this->filename);
            }
            if (file_exists($this->filename) && !is_writable($this->filename))
            {
                throw new AccessDeniedException($this->filename, 'write');
            }
            try {
                $this->driver->save($this->filename, $this->data);
            } catch (Throwable $t) {
                throw new ConfigurationSaveFailedException(get_class($this->driver), $t);
            }
        }
    }
}