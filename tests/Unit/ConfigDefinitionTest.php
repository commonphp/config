<?php

declare(strict_types=1);

namespace CommonPHP\Config\Tests\Unit;

use CommonPHP\Config\ConfigDefinition;
use CommonPHP\Config\Exceptions\ConfigException;
use CommonPHP\Config\Tests\Fixtures\FixtureSchema;
use PHPUnit\Framework\TestCase;

final class ConfigDefinitionTest extends TestCase
{
    public function testConstructStoresFileDefinitionState(): void
    {
        $schema = new FixtureSchema(['name' => 'required|string']);
        $definition = new ConfigDefinition(
            'config/app.json',
            'json',
            ['debug' => false],
            $schema,
            false,
            false,
            false,
        );

        self::assertSame('config/app.json', $definition->path());
        self::assertSame('json', $definition->format());
        self::assertSame(['debug' => false], $definition->defaults());
        self::assertSame($schema, $definition->schema());
        self::assertFalse($definition->isRequired());
        self::assertFalse($definition->isWritable());
        self::assertFalse($definition->shouldMergeDefaults());
    }

    public function testFileFactoryUsesExpectedDefaults(): void
    {
        $definition = ConfigDefinition::file('config/app.php');

        self::assertSame('config/app.php', $definition->path());
        self::assertNull($definition->format());
        self::assertSame([], $definition->defaults());
        self::assertNull($definition->schema());
        self::assertTrue($definition->isRequired());
        self::assertTrue($definition->isWritable());
        self::assertTrue($definition->shouldMergeDefaults());
    }

    public function testImmutableModifiersReturnUpdatedDefinitions(): void
    {
        $schema = new FixtureSchema(['name' => 'required|string']);
        $replacementSchema = new FixtureSchema(['enabled' => 'boolean']);
        $definition = ConfigDefinition::file('config/app.json', 'json', ['debug' => false], $schema);

        $withPath = $definition->withPath('config/app.php');
        $withFormat = $definition->withFormat('php');
        $withDefaults = $definition->withDefaults(['debug' => true], false);
        $withSchema = $definition->withSchema($replacementSchema);
        $optional = $definition->optional();
        $required = $optional->required();
        $readonly = $definition->writable(false);

        self::assertNotSame($definition, $withPath);
        self::assertSame('config/app.json', $definition->path());
        self::assertSame('config/app.php', $withPath->path());
        self::assertSame('php', $withFormat->format());
        self::assertSame(['debug' => true], $withDefaults->defaults());
        self::assertFalse($withDefaults->shouldMergeDefaults());
        self::assertSame($replacementSchema, $withSchema->schema());
        self::assertFalse($optional->isRequired());
        self::assertTrue($required->isRequired());
        self::assertFalse($readonly->isWritable());
    }

    public function testEmptyPathIsRejected(): void
    {
        $this->expectException(ConfigException::class);

        new ConfigDefinition('   ');
    }
}
