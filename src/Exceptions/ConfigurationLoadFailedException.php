<?php

namespace CommonPHP\Configuration\Exceptions;

use Throwable;

class ConfigurationLoadFailedException extends ConfigurationException
{
    public function __construct(string $driverClass, ?Throwable $previous = null)
    {
        parent::__construct('Could not load configuration using '.$driverClass, $previous);
        $this->code = 1610;
    }
}