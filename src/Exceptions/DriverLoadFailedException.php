<?php

namespace CommonPHP\Configuration\Exceptions;

use Throwable;

class DriverLoadFailedException extends ConfigurationException
{
    public function __construct(string $driverClass, ?Throwable $previous = null)
    {
        parent::__construct('Could not load configuration driver: '.$driverClass, $previous);
        $this->code = 1602;
    }
}