<?php

declare(strict_types=1);

namespace CommonPHP\Config\Tests\Unit;

use CommonPHP\Config\ConfigRepository;
use CommonPHP\Config\ConfigValue;
use CommonPHP\Config\Exceptions\ConfigException as ConfigBaseException;
use CommonPHP\Config\Exceptions\ConfigNotFoundException;
use PHPUnit\Framework\TestCase;

final class ConfigRepositoryTest extends TestCase
{
    public function testStoresAndReplacesCompleteConfigurationArrays(): void
    {
        $repository = new ConfigRepository(['app' => ['name' => 'demo']]);
        $fromArray = ConfigRepository::fromArray(['debug' => true]);

        self::assertSame(['app' => ['name' => 'demo']], $repository->all());
        self::assertSame(['debug' => true], $fromArray->all());

        $result = $repository->replace(['database' => ['host' => 'localhost']]);

        self::assertSame($repository, $result);
        self::assertSame(['database' => ['host' => 'localhost']], $repository->all());
    }

    public function testMergeRecursesAssociativeArraysAndReplacesLists(): void
    {
        $repository = new ConfigRepository([
            'database' => [
                'host' => 'localhost',
                'ports' => [3306, 3307],
                'options' => [
                    'ssl' => false,
                    'timeout' => 3,
                ],
            ],
        ]);

        $repository->merge([
            'database' => [
                'ports' => [3308],
                'options' => [
                    'ssl' => true,
                ],
            ],
        ]);

        self::assertSame([
            'database' => [
                'host' => 'localhost',
                'ports' => [3308],
                'options' => [
                    'ssl' => true,
                    'timeout' => 3,
                ],
            ],
        ], $repository->all());
    }

    public function testHasAndGetSupportRootDirectKeysAndDotNotation(): void
    {
        $repository = new ConfigRepository([
            'literal.key' => 'literal',
            'app' => [
                'name' => 'demo',
                'debug' => null,
            ],
        ]);

        self::assertTrue($repository->has(''));
        self::assertTrue($repository->has('literal.key'));
        self::assertTrue($repository->has('app.name'));
        self::assertTrue($repository->has('app.debug'));
        self::assertFalse($repository->has('app.missing'));
        self::assertSame($repository->all(), $repository->get(''));
        self::assertSame('literal', $repository->get('literal.key'));
        self::assertSame('demo', $repository->get('app.name'));
        self::assertNull($repository->get('app.debug'));
        self::assertSame('fallback', $repository->get('app.missing', 'fallback'));
    }

    public function testGetRequiredThrowsForMissingKeys(): void
    {
        $repository = new ConfigRepository();

        $this->expectException(ConfigNotFoundException::class);

        $repository->getRequired('missing');
    }

    public function testSetCreatesNestedArraysAndCanReplaceRoot(): void
    {
        $repository = new ConfigRepository(['app' => 'old']);

        $repository->set('app.name', 'demo');
        $repository->set('database.host', 'localhost');

        self::assertSame([
            'app' => ['name' => 'demo'],
            'database' => ['host' => 'localhost'],
        ], $repository->all());

        $repository->set('', ['root' => true]);

        self::assertSame(['root' => true], $repository->all());
    }

    public function testSetRootRejectsNonArrayValues(): void
    {
        $repository = new ConfigRepository();

        $this->expectException(ConfigBaseException::class);

        $repository->set('', 'not an array');
    }

    public function testEmptyWhitespaceKeysAreRejected(): void
    {
        $repository = new ConfigRepository();

        $this->expectException(ConfigBaseException::class);

        $repository->get('   ');
    }

    public function testRemoveHandlesDirectNestedMissingAndRootKeys(): void
    {
        $repository = new ConfigRepository([
            'literal.key' => 'literal',
            'app' => [
                'name' => 'demo',
                'debug' => true,
            ],
        ]);

        $repository->remove('literal.key');
        $repository->remove('app.debug');
        $repository->remove('app.missing');

        self::assertSame(['app' => ['name' => 'demo']], $repository->all());

        $repository->remove('');

        self::assertSame([], $repository->all());
    }

    public function testClearKeysCountAndIteratorExposeTopLevelConfig(): void
    {
        $repository = new ConfigRepository([
            'app' => ['name' => 'demo'],
            'debug' => true,
        ]);

        self::assertSame(['app', 'debug'], $repository->keys());
        self::assertCount(2, $repository);
        self::assertSame($repository->all(), iterator_to_array($repository));

        $repository->clear();

        self::assertSame([], $repository->all());
        self::assertCount(0, $repository);
    }

    public function testArrayAccessReadsWritesAppendsAndUnsetsValues(): void
    {
        $repository = new ConfigRepository(['app' => ['name' => 'demo']]);

        self::assertTrue(isset($repository['app.name']));
        self::assertSame('demo', $repository['app.name']);
        self::assertNull($repository[new \stdClass()]);

        $repository['app.debug'] = true;
        $repository[] = 'appended';
        unset($repository['app.name']);
        unset($repository[new \stdClass()]);

        self::assertSame([
            'app' => ['debug' => true],
            0 => 'appended',
        ], $repository->all());
    }

    public function testArrayAccessRejectsInvalidWriteOffsets(): void
    {
        $repository = new ConfigRepository();

        $this->expectException(ConfigBaseException::class);

        $repository[new \stdClass()] = 'invalid';
    }

    public function testValueWrapsFoundAndMissingValues(): void
    {
        $repository = new ConfigRepository(['app' => ['name' => 'demo']]);

        $found = $repository->value('app.name');
        $missing = $repository->value('app.missing', 'fallback');

        self::assertInstanceOf(ConfigValue::class, $found);
        self::assertTrue($found->exists());
        self::assertSame('demo', $found->raw());
        self::assertFalse($missing->exists());
        self::assertSame('fallback', $missing->get());
        self::assertSame('override', $missing->get('override'));
    }
}
