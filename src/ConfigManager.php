<?php

namespace Neuron\Configuration;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use Neuron\Configuration\Exceptions\DuplicateKeyException;
use Neuron\Configuration\Exceptions\DuplicateParserException;
use Neuron\Configuration\Exceptions\InvalidParserException;
use Neuron\Configuration\Exceptions\UnsupportedSourceException;
use Neuron\Configuration\Parsers\JsonParser;
use Neuron\Configuration\Parsers\PhpParser;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Serializable;
use Traversable;

/**
 * Manages configuration loading, merging, and retrieval.
 */
final class ConfigManager implements IteratorAggregate, ArrayAccess, Serializable, ConfigCollectionInterface
{
    private ConfigCollection $items;
    public readonly ParserRegistry $parsers;

    /**
     * Initializes the ConfigManager with optional default parsers.
     *
     * @param ContainerInterface $container The dependency injection container.
     * @param bool $registerDefaults Whether to register default parsers (JSON, PHP).
     * @throws DuplicateParserException
     * @throws InvalidParserException
     */
    public function __construct(ContainerInterface $container, bool $registerDefaults = true)
    {
        $this->items = new ConfigCollection();
        $this->parsers = new ParserRegistry($container);

        if ($registerDefaults) {
            $this->parsers->register(JsonParser::class, 'json');
            $this->parsers->register(PhpParser::class, 'php');
        }
    }

    /**
     * Imports configuration data with a specified merge strategy.
     *
     * @param string $key Configuration key.
     * @param array $data Configuration data to import.
     * @param MergeMode $mergeMode Merge behavior.
     * @throws DuplicateKeyException If the key exists and merge mode is set to error.
     */
    public function import(string $key, array $data, MergeMode $mergeMode = MergeMode::Replace): void
    {
        $exists = $this->offsetExists($key);
        if ($exists && $mergeMode == MergeMode::Error) {
            throw new DuplicateKeyException($key);
        }
        if ($exists && $mergeMode == MergeMode::Ignore) return;
        if ($mergeMode == MergeMode::Replace) {
            $this->items->set($key, $data);
        } else {
            $this->items->set($key, array_merge_recursive($this->items->get($key), $data));
        }
    }

    /**
     * Loads configuration from a file and imports it.
     *
     * @param string $key Configuration key.
     * @param string $source Configuration file path.
     * @param MergeMode $mergeMode Merge behavior.
     * @throws DuplicateKeyException
     * @throws UnsupportedSourceException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function load(string $key, string $source, MergeMode $mergeMode = MergeMode::Replace): void
    {
        $parser = $this->parsers->resolve($source);
        $data = $parser->read($source);
        $this->import($key, $data, $mergeMode);
    }

    /**
     * Returns an iterator for traversing the configuration items.
     *
     * @return Traversable An iterator for configuration items.
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items->toArray());
    }

    /**
     * Checks if a configuration key exists.
     *
     * @param mixed $offset Configuration key.
     * @return bool True if the key exists, false otherwise.
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->items->isset($offset);
    }

    /**
     * Retrieves a configuration value.
     *
     * @param mixed $offset Configuration key.
     * @return mixed The corresponding configuration value.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->items->get($offset);
    }

    /**
     * Sets a configuration value.
     *
     * @param mixed $offset Configuration key.
     * @param mixed $value Value to set.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->items->set($offset, $value);
    }

    /**
     * Removes a configuration key.
     *
     * @param mixed $offset Configuration key.
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->items->unset($offset);
    }

    /**
     * Serializes the configuration data.
     *
     * @return string Serialized configuration data.
     */
    public function serialize(): string
    {
        return serialize($this->items->toArray());
    }

    /**
     * Unserializes and restores configuration data.
     *
     * @param string $data Serialized configuration data.
     */
    public function unserialize(string $data): void
    {
        $this->items = new ConfigCollection(unserialize($data));
    }

    /**
     * Serializes configuration data into an array.
     *
     * @return array The serialized configuration data.
     */
    public function __serialize(): array
    {
        return $this->items->toArray();
    }

    /**
     * Unserializes configuration data from an array.
     *
     * @param array $data Configuration data.
     */
    public function __unserialize(array $data): void
    {
        $this->items = new ConfigCollection($data);
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, mixed $value): void
    {
        $this->items->set($key, $value);
    }

    /**
     * @inheritDoc
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->items->get($key, $default);
    }

    /**
     * @inheritDoc
     */
    public function isset(string $key): bool
    {
        return $this->items->isset($key);
    }

    /**
     * @inheritDoc
     */
    public function unset(string $key): void
    {
        $this->items->unset($key);
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->items->toArray();
    }
}