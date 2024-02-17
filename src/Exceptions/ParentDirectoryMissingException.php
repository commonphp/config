<?php

namespace CommonPHP\Configuration\Exceptions;

use Throwable;

class ParentDirectoryMissingException extends ConfigurationException
{
    public function __construct(string $path, ?Throwable $previous = null)
    {
        parent::__construct('The parent directory for the file \''.$path.'\' does not exist and could not be created', $previous);
        $this->code = 1607;
    }
}