<?php

namespace CommonPHP\Configuration\Exceptions;

use Throwable;

class ConfigurationSaveFailedException extends ConfigurationException
{
    public function __construct(string $driverClass, ?Throwable $previous = null)
    {
        parent::__construct('Could not save configuration using '.$driverClass, $previous);
        $this->code = 1609;
    }
}