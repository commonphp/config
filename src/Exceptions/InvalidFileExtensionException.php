<?php

namespace CommonPHP\Configuration\Exceptions;

use Throwable;

class InvalidFileExtensionException extends ConfigurationException
{
    public function __construct(string $driverClass, string $extension, ?Throwable $previous = null)
    {
        parent::__construct('The extension \''.$extension.'\' is not a valid extension. Supplied by: '.$driverClass, $previous);
        $this->code = 1603;
    }
}