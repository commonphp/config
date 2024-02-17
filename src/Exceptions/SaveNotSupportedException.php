<?php

namespace CommonPHP\Configuration\Exceptions;

use Throwable;

class SaveNotSupportedException extends ConfigurationException
{
    public function __construct(string $driverClass, ?Throwable $previous = null)
    {
        parent::__construct('The specified driver does not support save operations: '.$driverClass, $previous);
        $this->code = 1606;
    }
}