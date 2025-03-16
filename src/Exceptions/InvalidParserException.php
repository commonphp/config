<?php

namespace Neuron\Configuration\Exceptions;

use Neuron\Configuration\ConfigException;
use Neuron\Configuration\ParserInterface;
use Throwable;

/**
 * Exception thrown when an invalid parser class is registered.
 */
class InvalidParserException extends ConfigException
{
    /**
     * @param string $parser The invalid parser class name.
     * @param int $code Error code.
     * @param Throwable|null $previous Previous exception.
     */
    public function __construct(string $parser, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('The parser class `' . $parser . '` either does not exist or does not implement the ' . ParserInterface::class . ' interface', $code, $previous);
    }
}