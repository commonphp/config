<?php

namespace Neuron\Configuration;

use Neuron\Configuration\Exceptions\FileAccessException;
use Neuron\Configuration\Exceptions\FileMissingException;
use Neuron\Configuration\Exceptions\FileReadException;
use Neuron\Configuration\Exceptions\FileTypeException;
use Neuron\Configuration\Exceptions\FileWriteException;

/**
 * Abstract base class for configuration parsers.
 * Provides file validation and basic read/write operations.
 */
abstract class AbstractParser implements ParserInterface
{
    /**
     * Validates if a file can be read.
     *
     * @param string $source Path to the file.
     * @throws FileMissingException If the file is missing.
     * @throws FileTypeException If the path is not a file.
     * @throws FileAccessException If the file is not readable.
     */
    protected final function validateRead(string $source): void
    {
        if (!file_exists($source)) {
            throw new FileMissingException($source);
        }
        if (!is_file($source)) {
            throw new FileTypeException($source);
        }
        if (!is_readable($source)) {
            throw new FileAccessException($source, 'read');
        }
    }

    /**
     * Validates if a file can be written.
     *
     * @param string $target Path to the target file.
     * @throws FileWriteException If the file cannot be written.
     * @throws FileTypeException If the path is not a valid directory.
     */
    protected final function validateWrite(string $target): void
    {
        $dir = dirname($target);
        if (!file_exists($dir)) {
            if (!@mkdir($dir, 0777, true))
            {
                throw new FileWriteException($target);
            }
        }
        if (!is_dir($dir)) {
            throw new FileTypeException($dir, 'directory');
        }
        if (!is_writable($dir) || (file_exists($target) && is_file($target) && !is_writable($target))) {
            throw new FileWriteException($target);
        }
    }

    /**
     * @inheritDoc
     * @throws FileReadException If the file cannot be read.
     * @throws FileAccessException
     * @throws FileMissingException
     * @throws FileTypeException
     */
    public function read(string $source): array
    {
        $this->validateRead($source);
        $data = file_get_contents($source);
        if ($data === false) {
            throw new FileReadException($source);
        }
        return $this->deserialize($data);
    }

    /**
     * @inheritDoc
     * @throws FileWriteException If the file cannot be written.
     * @throws FileTypeException
     */
    public function write(array $data, string $target): void
    {
        $this->validateWrite($target);
        if (!file_put_contents($target, $this->serialize($data))) {
            throw new FileWriteException($target);
        }
    }
}