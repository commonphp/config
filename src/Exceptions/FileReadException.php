<?php

namespace Neuron\Configuration\Exceptions;

use Neuron\Configuration\ConfigException;
use Neuron\Configuration\ParserIdentifierType;
use Neuron\Configuration\ParserInterface;
use Throwable;

/**
 * Exception thrown when a configuration file cannot be read.
 */
class FileReadException extends ConfigException
{
    /**
     * @param string $file The unreadable file path.
     * @param int $code Error code.
     * @param Throwable|null $previous Previous exception.
     */
    public function __construct(string $file, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('The configuration file `' . $file . '` could not be read', $code, $previous);
    }
}