<?php

namespace Neuron\Configuration\Exceptions;

use Neuron\Configuration\ConfigException;
use Neuron\Configuration\ParserIdentifierType;
use Neuron\Configuration\ParserInterface;
use Throwable;

/**
 * Exception thrown when file access is denied.
 */
class FileAccessException extends ConfigException
{
    /**
     * @param string $file The file path.
     * @param string $access Type of access denied (read/write).
     * @param int $code Error code.
     * @param Throwable|null $previous Previous exception.
     */
    public function __construct(string $file, string $access, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct( ucfirst(strtolower($access)).' access denied to the configuration file `' . $file . '`', $code, $previous);
    }
}