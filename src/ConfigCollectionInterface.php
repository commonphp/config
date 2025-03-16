<?php

namespace Neuron\Configuration;

interface ConfigCollectionInterface
{
    /**
     * Sets a configuration value using dot notation.
     *
     * @param string $key Configuration key.
     * @param mixed $value Value to set.
     */
    public function set(string $key, mixed $value): void;

    /**
     * Retrieves a configuration value using dot notation.
     *
     * @param string $key Configuration key.
     * @param mixed $default Default value if key does not exist.
     * @return mixed The configuration value.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Checks if a configuration key exists using dot notation.
     *
     * @param string $key Configuration key.
     * @return bool True if key exists, false otherwise.
     */
    public function isset(string $key): bool;

    /**
     * Removes a configuration key using dot notation.
     *
     * @param string $key Configuration key to remove.
     */
    public function unset(string $key): void;

    /**
     * Returns the configuration data as an array.
     *
     * @return array The configuration data.
     */
    public function toArray(): array;

}