<?php

declare(strict_types=1);

namespace CommonPHP\Config\Tests\Unit;

use CommonPHP\Config\ConfigValue;
use CommonPHP\Config\Exceptions\ConfigValidationException;
use PHPUnit\Framework\TestCase;

final class ConfigValueTest extends TestCase
{
    public function testFoundAndMissingValuesExposeState(): void
    {
        $found = ConfigValue::found('app.name', 'demo');
        $missing = ConfigValue::missing('app.name', 'fallback');

        self::assertSame('app.name', $found->key());
        self::assertTrue($found->exists());
        self::assertSame('demo', $found->raw());
        self::assertSame('demo', $found->get('ignored'));
        self::assertFalse($missing->exists());
        self::assertSame('fallback', $missing->raw());
        self::assertSame('fallback', $missing->get());
        self::assertSame('override', $missing->get('override'));
    }

    public function testNullDetectionUsesResolvedValue(): void
    {
        self::assertTrue(ConfigValue::found('value', null)->isNull());
        self::assertFalse(ConfigValue::missing('value', 'fallback')->isNull());
    }

    public function testStringConversionAcceptsScalarValues(): void
    {
        self::assertSame('demo', ConfigValue::found('value', 'demo')->asString());
        self::assertSame('42', ConfigValue::found('value', 42)->asString());
        self::assertSame('1', ConfigValue::found('value', true)->asString());
        self::assertNull(ConfigValue::found('value', null)->asString());
        self::assertSame('fallback', ConfigValue::missing('value')->asString('fallback'));
    }

    public function testStringConversionRejectsNonScalarValues(): void
    {
        $this->expectException(ConfigValidationException::class);

        ConfigValue::found('value', ['demo'])->asString();
    }

    public function testIntegerConversionAcceptsIntegersAndIntegerStrings(): void
    {
        self::assertSame(42, ConfigValue::found('value', 42)->asInt());
        self::assertSame(-42, ConfigValue::found('value', ' -42 ')->asInt());
        self::assertNull(ConfigValue::found('value', null)->asInt());
        self::assertSame(7, ConfigValue::missing('value')->asInt(7));
    }

    public function testIntegerConversionRejectsNonIntegerValues(): void
    {
        $this->expectException(ConfigValidationException::class);

        ConfigValue::found('value', '1.2')->asInt();
    }

    public function testFloatConversionAcceptsNumericValues(): void
    {
        self::assertSame(42.0, ConfigValue::found('value', 42)->asFloat());
        self::assertSame(1.5, ConfigValue::found('value', '1.5')->asFloat());
        self::assertNull(ConfigValue::found('value', null)->asFloat());
        self::assertSame(2.5, ConfigValue::missing('value')->asFloat(2.5));
    }

    public function testFloatConversionRejectsNonNumericValues(): void
    {
        $this->expectException(ConfigValidationException::class);

        ConfigValue::found('value', 'nope')->asFloat();
    }

    public function testBooleanConversionAcceptsBooleansIntegersAndBooleanStrings(): void
    {
        self::assertTrue(ConfigValue::found('value', true)->asBool());
        self::assertFalse(ConfigValue::found('value', 0)->asBool());
        self::assertTrue(ConfigValue::found('value', 'yes')->asBool());
        self::assertFalse(ConfigValue::found('value', 'off')->asBool());
        self::assertNull(ConfigValue::found('value', null)->asBool());
        self::assertTrue(ConfigValue::missing('value')->asBool(true));
    }

    public function testBooleanConversionRejectsUnknownStrings(): void
    {
        $this->expectException(ConfigValidationException::class);

        ConfigValue::found('value', 'maybe')->asBool();
    }

    public function testArrayConversionAcceptsArraysAndRejectsOtherValues(): void
    {
        self::assertSame(['name' => 'demo'], ConfigValue::found('value', ['name' => 'demo'])->asArray());
        self::assertNull(ConfigValue::found('value', null)->asArray());
        self::assertSame(['fallback'], ConfigValue::missing('value')->asArray(['fallback']));

        $this->expectException(ConfigValidationException::class);

        ConfigValue::found('value', 'not array')->asArray();
    }

    public function testStringCastHandlesNullScalarsArraysAndUnencodableValues(): void
    {
        $resource = fopen('php://temp', 'r+');

        self::assertIsResource($resource);

        try {
            self::assertSame('', (string) ConfigValue::found('value', null));
            self::assertSame('demo', (string) ConfigValue::found('value', 'demo'));
            self::assertSame('{"name":"demo"}', (string) ConfigValue::found('value', ['name' => 'demo']));
            self::assertSame('resource (stream)', (string) ConfigValue::found('value', $resource));
        } finally {
            fclose($resource);
        }
    }
}
