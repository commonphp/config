<?php

/**
 * Configuration Manager for CommonPHP
 *
 * Manages configuration loading and saving through various drivers, supporting dynamic driver loading and extension handling.
 *
 * @package CommonPHP\Configuration
 * @author Timothy McClatchey <timothy@commonphp.org>
 * @copyright 2024 CommonPHP.org
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace CommonPHP\Configuration;

use CommonPHP\Configuration\Attributes\ConfigurationDriverAttribute;
use CommonPHP\Configuration\Contracts\ConfigurationDriverContract;
use CommonPHP\Configuration\Exceptions\AccessDeniedException;
use CommonPHP\Configuration\Exceptions\ConfigurationLoadFailedException;
use CommonPHP\Configuration\Exceptions\DriverConfigurationFailedException;
use CommonPHP\Configuration\Exceptions\DriverLoadFailedException;
use CommonPHP\Configuration\Exceptions\DuplicateFileExtensionException;
use CommonPHP\Configuration\Exceptions\ExtensionNotSupportedException;
use CommonPHP\Configuration\Exceptions\InvalidFileExtensionException;
use CommonPHP\Configuration\Support\Configuration;
use CommonPHP\Configuration\Support\ConfigurationCannotSaveBehavior;
use CommonPHP\Configuration\Support\DuplicateExtensionBehavior;
use CommonPHP\DependencyInjection\Exceptions\ClassNotDefinedException;
use CommonPHP\DependencyInjection\Exceptions\ClassNotInstantiableException;
use CommonPHP\DependencyInjection\Exceptions\InstantiateCircularReferenceException;
use CommonPHP\DependencyInjection\Exceptions\InstantiationFailedException;
use CommonPHP\DependencyInjection\Exceptions\ParameterDiscoveryFailedException;
use CommonPHP\DependencyInjection\Exceptions\ParameterTypeRequiredException;
use CommonPHP\DependencyInjection\Exceptions\UnsupportedReflectionTypeException;
use CommonPHP\Drivers\DriverManager;
use CommonPHP\Drivers\Exceptions\DriverException;
use CommonPHP\Drivers\Exceptions\NotConfiguredException;
use CommonPHP\Drivers\Exceptions\NotEnabledException;
use ReflectionClass;
use ReflectionException;

final class ConfigurationManager
{
    /** @var DuplicateExtensionBehavior How to handle duplicate file extensions. */
    public DuplicateExtensionBehavior $duplicateExtensionBehavior = DuplicateExtensionBehavior::OVERWRITE;

    /** @var ConfigurationCannotSaveBehavior Behavior when a configuration cannot be saved. */
    public ConfigurationCannotSaveBehavior $configurationCannotSaveBehavior = ConfigurationCannotSaveBehavior::THROW;

    /** @var DriverManager The DriverManager instance. */
    private DriverManager $driverManager;

    /** @var Configuration[] Array of loaded configurations. */
    private array $configurations = [];

    /** @var class-string<ConfigurationDriverContract>[] Mapping of file extensions to driver classes. */
    private array $extensions = [];

    /**
     * @throws DriverConfigurationFailedException
     */
    public function __construct(DriverManager $driverManager)
    {
        try {
            $driverManager->configure(ConfigurationDriverAttribute::class, ConfigurationDriverContract::class);
        } catch (DriverException $e) {
            throw new DriverConfigurationFailedException($e);
        }
        $this->driverManager = $driverManager;
    }

    /**
     * @param string $driverClass
     * @throws DriverLoadFailedException
     * @throws DuplicateFileExtensionException
     * @throws InvalidFileExtensionException
     */
    public function loadDriver(string $driverClass): void
    {
        try {
            if ($this->driverManager->isEnabled($driverClass)) return;
            $this->driverManager->enable($driverClass);
            $reflection = new ReflectionClass($driverClass);
        } catch (DriverException|ReflectionException $e) {
            throw new DriverLoadFailedException($driverClass, $e);
        }
        /** @var ConfigurationDriverAttribute $attribute */
        $attribute = $reflection->getAttributes(ConfigurationDriverAttribute::class)[0]->newInstance();
        foreach ($attribute->extensions as $extension)
        {
            $extension = strtolower($extension);
            if (!$this->isValidFileExtension($extension))
            {
                throw new InvalidFileExtensionException($driverClass, $extension);
            }
            if (isset($this->extensions[$extension]))
            {
                if ($this->duplicateExtensionBehavior == DuplicateExtensionBehavior::THROW)
                {
                    throw new DuplicateFileExtensionException($driverClass, $extension, $this->extensions[$extension]);
                }
                else if ($this->duplicateExtensionBehavior == DuplicateExtensionBehavior::WARN)
                {
                    trigger_error((new DuplicateFileExtensionException($driverClass, $extension, $this->extensions[$extension]))->getMessage(), E_USER_WARNING);
                }
                else if ($this->duplicateExtensionBehavior == DuplicateExtensionBehavior::NOTICE)
                {
                    trigger_error((new DuplicateFileExtensionException($driverClass, $extension, $this->extensions[$extension]))->getMessage());
                }
                else if ($this->duplicateExtensionBehavior == DuplicateExtensionBehavior::OVERWRITE)
                {
                    $this->extensions[$extension] = $driverClass;
                }
                // Do nothing on skip
            }
            else
            {
                $this->extensions[$extension] = $driverClass;
            }
        }
    }

    /**
     * @throws ExtensionNotSupportedException
     * @throws ClassNotDefinedException
     * @throws InstantiationFailedException
     * @throws ParameterDiscoveryFailedException
     * @throws InstantiateCircularReferenceException
     * @throws ParameterTypeRequiredException
     * @throws AccessDeniedException
     * @throws UnsupportedReflectionTypeException
     * @throws NotEnabledException
     * @throws NotConfiguredException
     * @throws ConfigurationLoadFailedException
     * @throws ClassNotInstantiableException
     */
    public function get(string $absolutePath): Configuration
    {
        if (isset($this->configurations[$absolutePath])) return $this->configurations[$absolutePath];
        $extension = pathinfo($absolutePath, PATHINFO_EXTENSION);
        $extensionLower = strtolower($extension);
        if (!isset($this->extensions[$extensionLower]))
        {
            throw new ExtensionNotSupportedException($extension);
        }
        $result = new Configuration($this, $this->driverManager->get($this->extensions[$extensionLower]), $absolutePath);
        $this->configurations[$absolutePath] = $result;
        return $result;
    }

    private function isValidFileExtension(string $extension): bool
    {
        return strlen(trim($extension)) > 0 && !preg_match('/[^a-z0-9._-]/', $extension);
    }
}