<?php

declare(strict_types=1);

namespace CommonPHP\Config\Tests\Unit;

use CommonPHP\Config\ConfigSchemaValidator;
use CommonPHP\Config\Exceptions\ConfigValidationException;
use CommonPHP\Config\Tests\Fixtures\FixtureSchema;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ConfigSchemaValidatorTest extends TestCase
{
    public function testValidateAcceptsStringAndArrayRuleDefinitions(): void
    {
        $validator = new ConfigSchemaValidator();

        $errors = $validator->validate([
            'app' => [
                'name' => 'demo',
                'debug' => true,
            ],
            'retries' => 3,
        ], [
            'app.name' => 'required|string',
            'app.debug' => ['required', 'boolean'],
            'retries' => ['rules' => 'required|integer'],
        ]);

        self::assertSame([], $errors);
    }

    public function testValidateReturnsMessagesForMissingAndWrongTypeValues(): void
    {
        $validator = new ConfigSchemaValidator();

        $errors = $validator->validate(['debug' => 'yes'], [
            'app.name' => 'required|string',
            'debug' => 'required|boolean',
        ]);

        self::assertCount(2, $errors);
        self::assertStringContainsString('Missing required configuration key: app.name.', $errors[0]);
        self::assertStringContainsString('debug must be boolean', $errors[1]);
    }

    public function testOptionalAndNullableRulesPermitMissingAndNullValues(): void
    {
        $validator = new ConfigSchemaValidator();

        self::assertSame([], $validator->validate(['name' => null], [
            'name' => 'required|nullable|string',
            'missing' => 'optional|string',
        ]));
    }

    public function testTypeAliasesAndSpecialTypesAreSupported(): void
    {
        $callable = static fn (): bool => true;
        $object = new stdClass();
        $validator = new ConfigSchemaValidator();

        $errors = $validator->validate([
            'bool' => true,
            'int' => 1,
            'double' => 1.5,
            'numeric_int' => 1,
            'numeric_float' => 1.5,
            'array' => ['key' => 'value'],
            'list' => ['value'],
            'scalar' => 'value',
            'callable' => $callable,
            'object' => $object,
            'class' => $object,
            'mixed' => $object,
            'null' => null,
        ], [
            'bool' => 'bool',
            'int' => 'int',
            'double' => 'double',
            'numeric_int' => 'numeric',
            'numeric_float' => 'number',
            'array' => 'array',
            'list' => 'list',
            'scalar' => 'scalar',
            'callable' => 'callable',
            'object' => 'object',
            'class' => stdClass::class,
            'mixed' => 'mixed',
            'null' => 'null',
        ]);

        self::assertSame([], $errors);
    }

    public function testAllowedEnumPatternAndCallbacksAreApplied(): void
    {
        $validator = new ConfigSchemaValidator();

        $errors = $validator->validate([
            'env' => 'production',
            'driver' => 'mysql',
            'name' => 'app_01',
            'port' => 3306,
            'host' => 'localhost',
        ], [
            'env' => ['allowed' => ['production', 'staging']],
            'driver' => ['enum' => ['mysql', 'pgsql']],
            'name' => ['pattern' => '/^app_[0-9]+$/'],
            'port' => static fn (mixed $value): bool => $value > 0,
            'host' => ['callback' => static fn (mixed $value, string $field): string => $field . ' is invalid'],
        ]);

        self::assertSame(['host is invalid'], $errors);
    }

    public function testInvalidAllowedPatternAndFalseCallbackProduceErrors(): void
    {
        $validator = new ConfigSchemaValidator();

        $errors = $validator->validate([
            'env' => 'local',
            'name' => 'bad',
            'port' => 0,
        ], [
            'env' => 'in:production,staging',
            'name' => ['pattern' => '/^app_[0-9]+$/'],
            'port' => static fn (mixed $value): bool => $value > 0,
        ]);

        self::assertCount(3, $errors);
        self::assertStringContainsString('unsupported value', $errors[0]);
        self::assertStringContainsString('required pattern', $errors[1]);
        self::assertStringContainsString('failed validation', $errors[2]);
    }

    public function testAssociativeTypeDefinitionsSupportTypeTypesRulesAllowedAndCallbacks(): void
    {
        $validator = new ConfigSchemaValidator();

        self::assertSame([], $validator->validate([
            'name' => 'demo',
            'mode' => 'safe',
            'count' => 2,
        ], [
            'name' => ['required' => true, 'type' => 'string'],
            'mode' => ['types' => ['string'], 'rules' => ['required'], 'allowed' => ['safe']],
            'count' => ['type' => 'integer', 'callback' => static fn (mixed $value): bool => $value === 2],
        ]));
    }

    public function testAssertValidThrowsWhenErrorsArePresent(): void
    {
        $validator = new ConfigSchemaValidator();

        $this->expectException(ConfigValidationException::class);

        $validator->assertValid([], ['name' => 'required|string']);
    }

    public function testSchemaObjectsDelegateRulesToValidator(): void
    {
        $schema = new FixtureSchema(['name' => 'required|string']);

        self::assertSame(['name' => 'required|string'], $schema->rules());
        self::assertSame([], $schema->validate(['name' => 'demo']));

        $schema->assertValid(['name' => 'demo']);

        $this->expectException(ConfigValidationException::class);

        $schema->assertValid([]);
    }
}
