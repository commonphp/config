<?php

namespace CommonPHP\Configuration\Exceptions;

use Throwable;

class AccessDeniedException extends ConfigurationException
{
    public function __construct(string $path, string $access, ?Throwable $previous = null)
    {
        parent::__construct('Access denied while trying to '.$access.' file '.$path, $previous);
        $this->code = 1611;
    }
}