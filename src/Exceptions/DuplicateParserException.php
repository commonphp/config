<?php

namespace Neuron\Configuration\Exceptions;

use Neuron\Configuration\ConfigException;
use Neuron\Configuration\ParserIdentifierType;
use Neuron\Configuration\ParserInterface;
use Throwable;

/**
 * Exception thrown when a parser is already registered for a given identifier.
 */
class DuplicateParserException extends ConfigException
{
    /**
     * @param string $file The conflicting parser file.
     * @param ParserIdentifierType $identifierType Identifier type (prefix/suffix).
     * @param int $code Error code.
     * @param Throwable|null $previous Previous exception.
     */
    public function __construct(string $file, ParserIdentifierType $identifierType, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('A parser class is already loaded that handles the `' . $file . '` '.strtolower($identifierType->name), $code, $previous);
    }
}