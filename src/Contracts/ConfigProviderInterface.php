<?php

declare(strict_types=1);

namespace CommonPHP\Config\Contracts;

use CommonPHP\Config\ConfigDefinition;

interface ConfigProviderInterface
{
    public function define(string $key, ConfigDefinition $definition): void;

    public function defineFile(
        string $key,
        string $path,
        ?string $format = null,
        array $defaults = [],
        ?ConfigSchemaInterface $schema = null,
        bool $required = true,
        bool $writable = true,
        bool $mergeDefaults = true,
    ): static;

    public function hasDefinition(string $key): bool;

    public function definition(string $key): ConfigDefinition;

    public function definitions(): array;

    public function registerDriver(string $format, string $driverClass, array $options = []): static;

    public function hasDriverFor(string $format): bool;

    public function driver(string $format): ConfigDriverInterface;

    public function load(string $key): array;

    public function loadAll(): ConfigRepositoryInterface;

    public function write(string $key, ?array $config = null): void;

    public function repository(): ConfigRepositoryInterface;

    public function get(string $key, mixed $default = null): mixed;
}
