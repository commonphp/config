<?php

namespace CommonPHP\Configuration\Exceptions;

use Throwable;

class ParentAccessDeniedException extends ConfigurationException
{
    public function __construct(string $path, ?Throwable $previous = null)
    {
        parent::__construct('The parent directory for the file \''.$path.'\' cannot be written to', $previous);
        $this->code = 1608;
    }
}