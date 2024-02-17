<?php

namespace CommonPHP\Configuration\Exceptions;

use Exception;
use Throwable;

class ConfigurationException extends Exception
{
    public function __construct(string $message = "", ?Throwable $previous = null)
    {
        parent::__construct($message, 1600, $previous);
    }
}