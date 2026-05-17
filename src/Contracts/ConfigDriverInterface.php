<?php

declare(strict_types=1);

namespace CommonPHP\Config\Contracts;

use CommonPHP\Runtime\Contracts\DriverInterface;

interface ConfigDriverInterface extends DriverInterface
{
    public function validate(string $data): bool;
    public function encode(array $config): string;
    public function decode(string $data): array;
    public function read(string $filename): array;
    public function write(string $filename, array $config): void;
}