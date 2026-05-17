<?php

declare(strict_types=1);

namespace CommonPHP\Config;

use CommonPHP\Runtime\Contracts\DriverPoolTrait;
use CommonPHP\Runtime\Contracts\PathResolverInterface;
use CommonPHP\Config\Contracts\ConfigProviderInterface;

final class ConfigProvider implements ConfigProviderInterface
{
    use DriverPoolTrait;

    private PathResolverInterface $pathResolver;
    public function __construct(PathResolverInterface $pathResolver)
    {
        $this->pathResolver = $pathResolver;
    }

    public function define(string $key, ConfigDefinition $definition): void
    {
        // TODO: Implement define() method.
    }
}