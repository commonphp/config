<?php

namespace CommonPHP\Configuration\Contracts;

use CommonPHP\Drivers\Contracts\DriverContract;

interface ConfigurationDriverContract extends DriverContract
{
    function canSave(): bool;

    function load(string $filename): array;

    function save(string $filename, array $data): void;
}