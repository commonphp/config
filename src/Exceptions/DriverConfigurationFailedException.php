<?php

namespace CommonPHP\Configuration\Exceptions;

use Throwable;

class DriverConfigurationFailedException extends ConfigurationException
{
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct('An expected error occurred while configuring the driver manager', $previous);
        $this->code = 1601;
    }
}