<?php

namespace Neuron\Configuration\Exceptions;

use Neuron\Configuration\ConfigException;
use Neuron\Configuration\ParserIdentifierType;
use Neuron\Configuration\ParserInterface;
use Throwable;

/**
 * Exception thrown when an invalid format is encountered in a configuration file.
 */
class FormatException extends ConfigException
{
    /**
     * @param string $format The invalid format type.
     * @param int $code Error code.
     * @param Throwable|null $previous Previous exception.
     */
    public function __construct(string $format, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('Could not deserialize raw '.$format.' data', $code, $previous);
    }
}