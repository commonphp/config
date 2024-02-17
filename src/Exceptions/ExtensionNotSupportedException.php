<?php

namespace CommonPHP\Configuration\Exceptions;

use Throwable;

class ExtensionNotSupportedException extends ConfigurationException
{
    public function __construct(string $extension, ?Throwable $previous = null)
    {
        parent::__construct('The extension \''.$extension.'\' does not seem to have a driver associated with it', $previous);
        $this->code = 1605;
    }
}