<?php

namespace Neuron\Configuration\Exceptions;

use Neuron\Configuration\ConfigException;
use Neuron\Configuration\ParserIdentifierType;
use Neuron\Configuration\ParserInterface;
use Throwable;

/**
 * Exception thrown when a configuration file cannot be written.
 */
class FileWriteException extends ConfigException
{
    /**
     * @param string $file The file path that could not be written.
     * @param int $code Error code.
     * @param Throwable|null $previous Previous exception.
     */
    public function __construct(string $file, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('The configuration file `' . $file . '` could not be written', $code, $previous);
    }
}