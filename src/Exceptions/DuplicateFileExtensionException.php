<?php

namespace CommonPHP\Configuration\Exceptions;

use Throwable;

class DuplicateFileExtensionException extends ConfigurationException
{
    public function __construct(string $driverClass, string $extension, string $registeredClass, ?Throwable $previous = null)
    {
        parent::__construct('The extension \''.$extension.'\' is already registered by '.$registeredClass.', Supplied by: '.$driverClass, $previous);
        $this->code = 1604;
    }
}