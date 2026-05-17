<?php

declare(strict_types=1);

namespace CommonPHP\Config;

use CommonPHP\Config\Contracts\ConfigDriverInterface;
use CommonPHP\Config\Contracts\ConfigRepositoryInterface;
use CommonPHP\Config\Exceptions\UnsupportedConfigFormatException;

final class ConfigLoader
{
    /**
     * @var array<string, ConfigDriverInterface>
     */
    private array $drivers = [];

    private ConfigRepositoryInterface $repository;

    public function __construct(?ConfigRepositoryInterface $repository = null, array $drivers = [])
    {
        $this->repository = $repository ?? new ConfigRepository();

        foreach ($drivers as $format => $driver) {
            if ($driver instanceof ConfigDriverInterface) {
                $this->registerDriver((string) $format, $driver);
            }
        }
    }

    public function repository(): ConfigRepositoryInterface
    {
        return $this->repository;
    }

    public function registerDriver(string $format, ConfigDriverInterface $driver): static
    {
        $this->drivers[$this->normalizeFormat($format)] = $driver;

        return $this;
    }

    public function addDriver(string $format, ConfigDriverInterface $driver): static
    {
        return $this->registerDriver($format, $driver);
    }

    public function hasDriver(string $format): bool
    {
        return isset($this->drivers[$this->normalizeFormat($format)]);
    }

    public function driver(string $format): ConfigDriverInterface
    {
        $format = $this->normalizeFormat($format);

        if (!isset($this->drivers[$format])) {
            throw new UnsupportedConfigFormatException('Unsupported configuration format: ' . $format);
        }

        return $this->drivers[$format];
    }

    public function formats(): array
    {
        return array_keys($this->drivers);
    }

    public function validate(string $data, string $format): bool
    {
        return $this->driver($format)->validate($data);
    }

    public function encode(array $config, string $format): string
    {
        return $this->driver($format)->encode($config);
    }

    public function decode(string $data, string $format): array
    {
        return $this->driver($format)->decode($data);
    }

    public function load(string $filename, ?string $format = null): array
    {
        return $this->driver($format ?? $this->formatFromPath($filename))->read($filename);
    }

    public function read(string $filename, ?string $format = null): array
    {
        return $this->load($filename, $format);
    }

    public function loadInto(string $filename, ?string $key = null, ?string $format = null): ConfigRepositoryInterface
    {
        $config = $this->load($filename, $format);

        if ($key === null || $key === '') {
            $this->repository->merge($config);
        } else {
            $this->repository->set($key, $config);
        }

        return $this->repository;
    }

    public function write(string $filename, array $config, ?string $format = null): void
    {
        $this->driver($format ?? $this->formatFromPath($filename))->write($filename, $config);
    }

    public function save(string $filename, array $config, ?string $format = null): void
    {
        $this->write($filename, $config, $format);
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
}
