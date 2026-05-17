<?php

declare(strict_types=1);

namespace CommonPHP\Config;

use ArrayAccess;
use ArrayIterator;
use CommonPHP\Config\Contracts\ConfigRepositoryInterface;
use CommonPHP\Config\Exceptions\ConfigException;
use CommonPHP\Config\Exceptions\ConfigNotFoundException;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements ArrayAccess<string|int, mixed>
 * @implements IteratorAggregate<string|int, mixed>
 */
class ConfigRepository implements ConfigRepositoryInterface, ArrayAccess, Countable, IteratorAggregate
{
    public function __construct(
        private array $config = [],
    ) {
    }

    public static function fromArray(array $config): self
    {
        return new self($config);
    }

    public function all(): array
    {
        return $this->config;
    }

    public function replace(array $config): static
    {
        $this->config = $config;

        return $this;
    }

    public function merge(array $config): static
    {
        $this->config = $this->mergeArrays($this->config, $config);

        return $this;
    }

    public function has(string $key): bool
    {
        if ($key === '') {
            return true;
        }

        if (array_key_exists($key, $this->config)) {
            return true;
        }

        $current = $this->config;

        foreach ($this->segments($key) as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return false;
            }

            $current = $current[$segment];
        }

        return true;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if ($key === '') {
            return $this->config;
        }

        if (array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }

        $current = $this->config;

        foreach ($this->segments($key) as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return $default;
            }

            $current = $current[$segment];
        }

        return $current;
    }

    public function getRequired(string $key): mixed
    {
        if (!$this->has($key)) {
            throw new ConfigNotFoundException('Configuration key was not found: ' . $key);
        }

        return $this->get($key);
    }

    public function value(string $key, mixed $default = null): ConfigValue
    {
        if (!$this->has($key)) {
            return ConfigValue::missing($key, $default);
        }

        return ConfigValue::found($key, $this->get($key));
    }

    public function set(string $key, mixed $value): static
    {
        if ($key === '') {
            if (!is_array($value)) {
                throw new ConfigException('The root configuration value must be an array.');
            }

            $this->config = $value;

            return $this;
        }

        $segments = $this->segments($key);
        $current = &$this->config;
        $last = array_pop($segments);

        foreach ($segments as $segment) {
            if (!isset($current[$segment]) || !is_array($current[$segment])) {
                $current[$segment] = [];
            }

            $current = &$current[$segment];
        }

        $current[$last] = $value;

        return $this;
    }

    public function remove(string $key): static
    {
        if ($key === '') {
            return $this->clear();
        }

        if (array_key_exists($key, $this->config)) {
            unset($this->config[$key]);

            return $this;
        }

        $segments = $this->segments($key);
        $current = &$this->config;
        $last = array_pop($segments);

        foreach ($segments as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return $this;
            }

            $current = &$current[$segment];
        }

        if (is_array($current)) {
            unset($current[$last]);
        }

        return $this;
    }

    public function clear(): static
    {
        $this->config = [];

        return $this;
    }

    public function keys(): array
    {
        return array_keys($this->config);
    }

    public function count(): int
    {
        return count($this->config);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->config);
    }

    public function offsetExists(mixed $offset): bool
    {
        return is_string($offset) || is_int($offset)
            ? $this->has((string) $offset)
            : false;
    }

    public function offsetGet(mixed $offset): mixed
    {
        return is_string($offset) || is_int($offset)
            ? $this->get((string) $offset)
            : null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->config[] = $value;

            return;
        }

        if (!is_string($offset) && !is_int($offset)) {
            throw new ConfigException('Configuration offsets must be strings or integers.');
        }

        $this->set((string) $offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        if (is_string($offset) || is_int($offset)) {
            $this->remove((string) $offset);
        }
    }

    private function segments(string $key): array
    {
        $segments = array_values(array_filter(
            array_map('trim', explode('.', $key)),
            static fn (string $segment): bool => $segment !== '',
        ));

        if ($segments === []) {
            throw new ConfigException('Configuration key cannot be empty.');
        }

        return $segments;
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
