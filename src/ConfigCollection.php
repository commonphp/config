<?php

namespace Neuron\Configuration;

/**
 * Represents a collection of configuration data with dot-notation access.
 */
final class ConfigCollection implements ConfigCollectionInterface
{
    private array $data;

    /**
     * Initializes the configuration collection with optional initial data.
     *
     * @param array $initialData Initial configuration data.
     */
    public function __construct(array $initialData = [])
    {
        $this->data = $initialData;
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $current = &$this->data;

        foreach ($keys as $segment) {
            if (!isset($current[$segment]) || !is_array($current[$segment])) {
                $current[$segment] = [];
            }
            $current = &$current[$segment];
        }

        $current = $value;
    }

    /**
     * @inheritDoc
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $current = $this->data;

        foreach ($keys as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return $default;
            }
            $current = $current[$segment];
        }

        return $current;
    }

    /**
     * @inheritDoc
     */
    public function isset(string $key): bool
    {
        $keys = explode('.', $key);
        $current = $this->data;

        foreach ($keys as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return false;
            }
            $current = $current[$segment];
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function unset(string $key): void
    {
        $keys = explode('.', $key);
        $current = &$this->data;

        while (count($keys) > 1) {
            $segment = array_shift($keys);
            if (!isset($current[$segment]) || !is_array($current[$segment])) {
                return;
            }
            $current = &$current[$segment];
        }

        unset($current[array_shift($keys)]);
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
