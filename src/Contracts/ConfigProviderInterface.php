<?php

declare(strict_types=1);

namespace CommonPHP\Config\Contracts;

use CommonPHP\Config\ConfigDefinition;

interface ConfigProviderInterface
{
    public function define(string $key, ConfigDefinition $definition): void;
}