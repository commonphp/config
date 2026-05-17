<?php

declare(strict_types=1);

namespace CommonPHP\Config\Contracts;

use CommonPHP\Config\ConfigValue;

interface ConfigRepositoryInterface
{
    public function all(): array;

    public function replace(array $config): static;

    public function merge(array $config): static;

    public function has(string $key): bool;

    public function get(string $key, mixed $default = null): mixed;

    public function getRequired(string $key): mixed;

    public function value(string $key, mixed $default = null): ConfigValue;

    public function set(string $key, mixed $value): static;

    public function remove(string $key): static;

    public function clear(): static;

    public function keys(): array;
}
