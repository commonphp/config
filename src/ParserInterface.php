<?php

namespace Neuron\Configuration;

/**
 * Interface for configuration parsers.
 */
interface ParserInterface
{
    /**
     * Deserializes raw data into an array.
     *
     * @param string $data The raw data to deserialize.
     * @return array The parsed configuration data.
     */
    public function deserialize(string $data): array;

    /**
     * Reads and deserializes a configuration file.
     *
     * @param string $source Path to the file.
     * @return array The deserialized configuration data.
     */
    public function read(string $source): array;

    /**
     * Serializes an array into a format suitable for storage.
     *
     * @param array $data The configuration data to serialize.
     * @return string The serialized data.
     */
    public function serialize(array $data): string;

    /**
     * Serializes and writes a configuration file.
     *
     * @param array $data Configuration data to write.
     * @param string $target Path to the target file.
     */
    public function write(array $data, string $target): void;
}