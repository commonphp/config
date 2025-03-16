<?php

namespace Neuron\Configuration\Exceptions;

use Neuron\Configuration\ConfigException;
use Neuron\Configuration\ParserIdentifierType;
use Neuron\Configuration\ParserInterface;
use Throwable;

/**
 * Exception thrown when a configuration key is duplicated.
 */
class DuplicateKeyException extends ConfigException
{
    /**
     * @param string $file The duplicated key.
     * @param int $code Error code.
     * @param Throwable|null $previous Previous exception.
     */
    public function __construct(string $file, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('The configuration key `' . $file . '` already exists', $code, $previous);
    }
}