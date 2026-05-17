<?php

declare(strict_types=1);

namespace CommonPHP\Config;

use CommonPHP\Config\Contracts\ConfigDriverInterface;
use CommonPHP\Config\Contracts\ConfigProviderInterface;
use CommonPHP\Config\Contracts\ConfigRepositoryInterface;
use CommonPHP\Config\Exceptions\ConfigDriverException;
use CommonPHP\Config\Exceptions\ConfigNotFoundException;
use CommonPHP\Config\Exceptions\ConfigValidationException;
use CommonPHP\Config\Exceptions\ConfigWriteException;
use CommonPHP\Config\Exceptions\UnsupportedConfigFormatException;
use CommonPHP\Runtime\Contracts\DriverPoolTrait;
use CommonPHP\Runtime\Contracts\PathResolverInterface;
use RuntimeException;

final class ConfigProvider implements ConfigProviderInterface
{
    use DriverPoolTrait;

    private PathResolverInterface $pathResolver;

    private ConfigRepositoryInterface $repository;

    private ConfigSchemaValidator $schemaValidator;

    /**
     * @var array<string, ConfigDefinition>
     */
    private array $definitions = [];

    /**
     * @var array<string, class-string<ConfigDriverInterface>>
     */
    private array $formats = [];

    public function __construct(
        PathResolverInterface $pathResolver,
        ?ConfigRepositoryInterface $repository = null,
        ?ConfigSchemaValidator $schemaValidator = null,
    ) {
        $this->pathResolver = $pathResolver;
        $this->repository = $repository ?? new ConfigRepository();
        $this->schemaValidator = $schemaValidator ?? new ConfigSchemaValidator();
        $this->enableDrivers(ConfigDriverInterface::class);
    }

    public function define(string $key, ConfigDefinition $definition): void
    {
        $key = trim($key);

        if ($key === '') {
            throw new ConfigValidationException('Configuration definition key cannot be empty.');
        }

        $this->definitions[$key] = $definition;
    }

    public function defineFile(
        string $key,
        string $path,
        ?string $format = null,
        array $defaults = [],
    ): static {
        $this->define($key, ConfigDefinition::file($path, $format, $defaults));

        return $this;
    }

    public function hasDefinition(string $key): bool
    {
        return isset($this->definitions[$key]);
    }

    public function definition(string $key): ConfigDefinition
    {
        if (!$this->hasDefinition($key)) {
            throw new ConfigNotFoundException('Configuration definition was not found: ' . $key);
        }

        return $this->definitions[$key];
    }

    public function definitions(): array
    {
        return $this->definitions;
    }

    public function registerDriver(string $format, string $driverClass, array $options = []): static
    {
        $format = $this->normalizeFormat($format);

        if ($this->hasDriverFor($format)) {
            if ($this->formats[$format] === $driverClass) {
                return $this;
            }

            throw new ConfigDriverException('Configuration format is already registered: ' . $format);
        }

        try {
            $this->addDriver($driverClass);
        } catch (RuntimeException $e) {
            if (!str_contains($e->getMessage(), 'already defined')) {
                throw new ConfigDriverException($e->getMessage(), $e->getCode(), $e);
            }
        }

        try {
            $this->useDriver($format, $driverClass, $options);
        } catch (RuntimeException $e) {
            throw new ConfigDriverException($e->getMessage(), $e->getCode(), $e);
        }

        $this->formats[$format] = $driverClass;

        return $this;
    }

    public function registerFormat(string $format, string $driverClass, array $options = []): static
    {
        return $this->registerDriver($format, $driverClass, $options);
    }

    public function formats(): array
    {
        return array_keys($this->formats);
    }

    public function hasDriverFor(string $format): bool
    {
        return isset($this->formats[$this->normalizeFormat($format)]);
    }

    public function driver(string $format): ConfigDriverInterface
    {
        $format = $this->normalizeFormat($format);

        if (!$this->hasDriverFor($format)) {
            throw new UnsupportedConfigFormatException('Unsupported configuration format: ' . $format);
        }

        $driver = $this->getDriver($format);

        if (!$driver instanceof ConfigDriverInterface) {
            throw new ConfigDriverException('Driver for ' . $format . ' is not a config driver.');
        }

        return $driver;
    }

    public function load(string $key): array
    {
        $definition = $this->definition($key);
        $path = $this->resolvePath($definition->path());
        $config = [];

        if (is_file($path)) {
            $config = $this->driver($definition->format() ?? $this->formatFromPath($path))->read($path);
        } elseif ($definition->isRequired()) {
            $this->driver($definition->format() ?? $this->formatFromPath($path))->read($path);
        }

        if ($definition->defaults() !== []) {
            $config = $definition->shouldMergeDefaults()
                ? $this->mergeArrays($definition->defaults(), $config)
                : $config + $definition->defaults();
        }

        if ($definition->schema() !== null) {
            $this->schemaValidator->assertValid($config, $definition->schema());
        }

        $this->repository->set($key, $config);

        return $config;
    }

    public function loadAll(): ConfigRepositoryInterface
    {
        foreach (array_keys($this->definitions) as $key) {
            $this->load($key);
        }

        return $this->repository;
    }

    public function write(string $key, ?array $config = null): void
    {
        $definition = $this->definition($key);

        if (!$definition->isWritable()) {
            throw new ConfigWriteException('Configuration definition is not writable: ' . $key);
        }

        $config ??= $this->repository->getRequired($key);

        if (!is_array($config)) {
            throw new ConfigValidationException('Configuration key must contain an array before it can be written: ' . $key);
        }

        if ($definition->schema() !== null) {
            $this->schemaValidator->assertValid($config, $definition->schema());
        }

        $path = $this->resolvePath($definition->path());
        $this->driver($definition->format() ?? $this->formatFromPath($path))->write($path, $config);
        $this->repository->set($key, $config);
    }

    public function repository(): ConfigRepositoryInterface
    {
        return $this->repository;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->repository->get($key, $default);
    }

    private function resolvePath(string $path): string
    {
        if ($this->isAbsolutePath($path)) {
            return $path;
        }

        return $this->pathResolver->resolve($path);
    }

    private function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, DIRECTORY_SEPARATOR)
            || preg_match('/^[A-Za-z]:[\/\\\\]/', $path) === 1
            || str_starts_with($path, '\\\\');
    }

    private function formatFromPath(string $path): string
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if ($extension === '') {
            throw new UnsupportedConfigFormatException(
                'Configuration format could not be inferred from path: ' . $path
            );
        }

        return $extension;
    }

    private function normalizeFormat(string $format): string
    {
        $format = strtolower(ltrim(trim($format), '.'));

        return match ($format) {
            'yml' => 'yaml',
            default => $format,
        };
    }

    private function mergeArrays(array $base, array $override): array
    {
        foreach ($override as $key => $value) {
            if (
                array_key_exists($key, $base)
                && is_array($base[$key])
                && is_array($value)
                && !array_is_list($base[$key])
                && !array_is_list($value)
            ) {
                $base[$key] = $this->mergeArrays($base[$key], $value);

                continue;
            }

            $base[$key] = $value;
        }

        return $base;
    }
}
