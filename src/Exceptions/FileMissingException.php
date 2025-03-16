<?php

namespace Neuron\Configuration\Exceptions;

use Neuron\Configuration\ConfigException;
use Neuron\Configuration\ParserIdentifierType;
use Neuron\Configuration\ParserInterface;
use Throwable;

/**
 * Exception thrown when a required configuration file is missing.
 */
class FileMissingException extends ConfigException
{
    /**
     * @param string $file The missing file path.
     * @param int $code Error code.
     * @param Throwable|null $previous Previous exception.
     */
    public function __construct(string $file, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('The configuration file `' . $file . '` does not exist', $code, $previous);
    }
}