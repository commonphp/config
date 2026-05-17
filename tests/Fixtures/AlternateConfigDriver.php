<?php

declare(strict_types=1);

namespace CommonPHP\Config\Tests\Fixtures;

use CommonPHP\Config\Contracts\AbstractConfigDriver;

final class AlternateConfigDriver extends AbstractConfigDriver
{
    public function validate(string $data): bool
    {
        return trim($data) !== '';
    }

    public function encode(array $config): string
    {
        return serialize($config);
    }

    public function decode(string $data): array
    {
        $decoded = unserialize($data, ['allowed_classes' => false]);

        return is_array($decoded) ? $decoded : [];
    }
}
