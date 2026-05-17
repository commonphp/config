<?php

declare(strict_types=1);

namespace CommonPHP\Config\Tests\Unit;

use CommonPHP\Config\Exceptions\ConfigDriverException;
use CommonPHP\Config\Exceptions\ConfigException;
use CommonPHP\Config\Exceptions\ConfigNotFoundException;
use CommonPHP\Config\Exceptions\ConfigParseException;
use CommonPHP\Config\Exceptions\ConfigReadException;
use CommonPHP\Config\Exceptions\ConfigSchemaException;
use CommonPHP\Config\Exceptions\ConfigValidationException;
use CommonPHP\Config\Exceptions\ConfigWriteException;
use CommonPHP\Config\Exceptions\UnsupportedConfigFormatException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ExceptionsTest extends TestCase
{
    public function testConfigExceptionIsRuntimeException(): void
    {
        self::assertInstanceOf(RuntimeException::class, new ConfigException('Failure.'));
    }

    public function testSpecificExceptionsExtendConfigException(): void
    {
        $exceptions = [
            new ConfigDriverException('Failure.'),
            new ConfigNotFoundException('Failure.'),
            new ConfigParseException('Failure.'),
            new ConfigReadException('Failure.'),
            new ConfigSchemaException('Failure.'),
            new ConfigValidationException('Failure.'),
            new ConfigWriteException('Failure.'),
            new UnsupportedConfigFormatException('Failure.'),
        ];

        foreach ($exceptions as $exception) {
            self::assertInstanceOf(ConfigException::class, $exception);
        }
    }
}
