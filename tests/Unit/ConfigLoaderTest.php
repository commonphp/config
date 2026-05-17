<?php

declare(strict_types=1);

namespace CommonPHP\Config\Tests\Unit;

use CommonPHP\Config\ConfigLoader;
use CommonPHP\Config\ConfigRepository;
use CommonPHP\Config\Exceptions\UnsupportedConfigFormatException;
use CommonPHP\Config\Tests\Fixtures\MemoryConfigDriver;
use CommonPHP\Config\Tests\Support\TemporaryDirectoryTrait;
use PHPUnit\Framework\TestCase;

final class ConfigLoaderTest extends TestCase
{
    use TemporaryDirectoryTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTemporaryDirectory('comphp_config_loader_');
    }

    protected function tearDown(): void
    {
        $this->removeTemporaryDirectory();

        parent::tearDown();
    }

    public function testConstructRegistersProvidedDriversAndIgnoresInvalidEntries(): void
    {
        $repository = new ConfigRepository();
        $loader = new ConfigLoader($repository, [
            'mem' => new MemoryConfigDriver(),
            'invalid' => new \stdClass(),
        ]);

        self::assertSame($repository, $loader->repository());
        self::assertTrue($loader->hasDriver('mem'));
        self::assertFalse($loader->hasDriver('invalid'));
        self::assertSame(['mem'], $loader->formats());
    }

    public function testRegisterDriverAddDriverAndYamlNormalization(): void
    {
        $loader = new ConfigLoader();
        $driver = new MemoryConfigDriver();

        $result = $loader->registerDriver('.MEM', $driver);
        $loader->addDriver('yml', $driver);

        self::assertSame($loader, $result);
        self::assertTrue($loader->hasDriver('mem'));
        self::assertTrue($loader->hasDriver('yaml'));
        self::assertSame($driver, $loader->driver('mem'));
        self::assertSame($driver, $loader->driver('yaml'));
    }

    public function testDriverThrowsForUnsupportedFormats(): void
    {
        $this->expectException(UnsupportedConfigFormatException::class);

        (new ConfigLoader())->driver('json');
    }

    public function testValidateEncodeAndDecodeDelegateToDriver(): void
    {
        $loader = (new ConfigLoader())->registerDriver('mem', new MemoryConfigDriver());
        $config = ['name' => 'demo'];
        $encoded = $loader->encode($config, 'mem');

        self::assertSame('{"name":"demo"}', $encoded);
        self::assertTrue($loader->validate($encoded, 'mem'));
        self::assertFalse($loader->validate('{invalid', 'mem'));
        self::assertSame($config, $loader->decode($encoded, 'mem'));
    }

    public function testLoadReadWriteAndSaveInferFormatFromExtension(): void
    {
        $loader = (new ConfigLoader())->registerDriver('mem', new MemoryConfigDriver());
        $path = $this->temporaryPath('config.mem');

        $loader->write($path, ['name' => 'demo']);

        self::assertSame(['name' => 'demo'], $loader->load($path));
        self::assertSame(['name' => 'demo'], $loader->read($path));

        $loader->save($path, ['name' => 'updated']);

        self::assertSame(['name' => 'updated'], $loader->read($path));
    }

    public function testExplicitFormatOverridesExtension(): void
    {
        $loader = (new ConfigLoader())->registerDriver('mem', new MemoryConfigDriver());
        $path = $this->temporaryPath('config.unknown');
        file_put_contents($path, '{"name":"demo"}');

        self::assertSame(['name' => 'demo'], $loader->load($path, 'mem'));
    }

    public function testLoadIntoMergesRootOrStoresUnderKey(): void
    {
        $repository = new ConfigRepository(['app' => ['debug' => false]]);
        $loader = new ConfigLoader($repository, ['mem' => new MemoryConfigDriver()]);
        $rootPath = $this->temporaryPath('root.mem');
        $keyPath = $this->temporaryPath('database.mem');
        file_put_contents($rootPath, '{"app":{"name":"demo"}}');
        file_put_contents($keyPath, '{"host":"localhost"}');

        $loader->loadInto($rootPath);
        $loader->loadInto($keyPath, 'database');

        self::assertSame([
            'app' => [
                'debug' => false,
                'name' => 'demo',
            ],
            'database' => [
                'host' => 'localhost',
            ],
        ], $repository->all());
    }

    public function testMissingExtensionCannotBeInferred(): void
    {
        $this->expectException(UnsupportedConfigFormatException::class);

        (new ConfigLoader())->load($this->temporaryPath('config'));
    }
}
