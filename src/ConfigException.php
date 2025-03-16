<?php

namespace Neuron\Configuration;

use Exception;
use Throwable;

/**
 * Base exception class for configuration-related errors.
 */
class ConfigException extends Exception
{
    /**
     * Constructs a ConfigException.
     *
     * @param string $message Exception message.
     * @param int $code Exception code.
     * @param Throwable|null $previous Previous exception.
     */
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}