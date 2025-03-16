<?php

namespace Neuron\Configuration\Exceptions;

use Neuron\Configuration\ConfigException;
use Neuron\Configuration\ParserInterface;
use Throwable;

/**
 * Exception thrown when no registered parser can handle a given source.
 */
class UnsupportedSourceException extends ConfigException
{
    /**
     * @param string $source The unsupported configuration source.
     * @param int $code Error code.
     * @param Throwable|null $previous Previous exception.
     */
    public function __construct(string $source, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('There is no configuration registered to handle the source `' . $source . '`', $code, $previous);
    }
}