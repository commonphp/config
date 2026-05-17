<?php

declare(strict_types=1);

namespace CommonPHP\Config\Tests\Fixtures;

use CommonPHP\Config\Contracts\AbstractConfigDriver;
use CommonPHP\Config\Exceptions\ConfigException;
use CommonPHP\Config\Exceptions\ConfigValidationException;
use JsonException;
use Throwable;

final class MemoryConfigDriver extends AbstractConfigDriver
{
    public function __construct(
        private readonly string $suffix = '',
    ) {
    }

    public function suffix(): string
    {
        return $this->suffix;
    }

    public function validate(string $data): bool
    {
        try {
            $this->decode($data);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function encode(array $config): string
    {
        if (($config['fail_encode'] ?? false) === true) {
            throw new ConfigException('Fixture encode failure.');
        }

        try {
            return json_encode($config, JSON_THROW_ON_ERROR) . $this->suffix;
        } catch (JsonException $e) {
            throw new ConfigException('Fixture could not encode config.', $e->getCode(), $e);
        }
    }

    public function decode(string $data): array
    {
        try {
            $decoded = json_decode(trim($data), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new ConfigValidationException('Fixture could not decode config.', $e->getCode(), $e);
        }

        if (!is_array($decoded)) {
            throw new ConfigValidationException('Fixture config must decode to an array.');
        }

        return $decoded;
    }
}
