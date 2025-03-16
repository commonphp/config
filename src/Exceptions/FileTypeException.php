<?php

namespace Neuron\Configuration\Exceptions;

use Neuron\Configuration\ConfigException;
use Neuron\Configuration\ParserIdentifierType;
use Neuron\Configuration\ParserInterface;
use Throwable;

/**
 * Exception thrown when an invalid file type is used.
 */
class FileTypeException extends ConfigException
{
    /**
     * @param string $file The file path.
     * @param string $type Expected file type.
     * @param int $code Error code.
     * @param Throwable|null $previous Previous exception.
     */
    public function __construct(string $file, string $type = 'file', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('The configuration target `' . $file . '` is not a '.$type, $code, $previous);
    }
}