<?php

declare(strict_types=1);

namespace CommonPHP\Config\Tests\Unit;

use CommonPHP\Config\ConfigDefinition;
use CommonPHP\Config\ConfigProvider;
use CommonPHP\Config\ConfigRepository;
use CommonPHP\Config\Exceptions\ConfigDriverException;
use CommonPHP\Config\Exceptions\ConfigNotFoundException;
use CommonPHP\Config\Exceptions\ConfigReadException;
use CommonPHP\Config\Exceptions\ConfigValidationException;
use CommonPHP\Config\Exceptions\ConfigWriteException;
use CommonPHP\Config\Exceptions\UnsupportedConfigFormatException;
use CommonPHP\Config\Tests\Fixtures\AlternateConfigDriver;
use CommonPHP\Config\Tests\Fixtures\FixtureSchema;
use CommonPHP\Config\Tests\Fixtures\MemoryConfigDriver;
use CommonPHP\Config\Tests\Support\TemporaryDirectoryTrait;
use CommonPHP\Runtime\Support\NativePathResolver;
use PHPUnit\Framework\TestCase;

final class ConfigProviderTest extends TestCase
{
    use TemporaryDirectoryTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTemporaryDirectory('comphp_config_provider_');
    }

    protected function tearDown(): void
    {
        $this->removeTemporaryDirectory();

        parent::tearDown();
    }

    public function testDefineStoresDefinitionsAndRejectsEmptyKeys(): void
    {
        $provider = $this->provider();
        $definition = ConfigDefinition::file('app.mem');

        $provider->define('app', $definition);

        self::assertTrue($provider->hasDefinition('app'));
        self::assertSame($definition, $provider->definition('app'));
        self::assertSame(['app' => $definition], $provider->definitions());

        $this->expectException(ConfigValidationException::class);

        $provider->define('   ', $definition);
    }

    public function testDefineFileIsAConvenienceMethod(): void
    {
        $provider = $this->provider();
        $schema = new FixtureSchema(['name' => 'required|string']);

        $result = $provider->defineFile(
            'app',
            'config/app.mem',
            'mem',
            ['name' => 'demo'],
            $schema,
            false,
            false,
            false,
        );

        self::assertSame($provider, $result);
        self::assertSame('config/app.mem', $provider->definition('app')->path());
        self::assertSame('mem', $provider->definition('app')->format());
        self::assertSame(['name' => 'demo'], $provider->definition('app')->defaults());
        self::assertSame($schema, $provider->definition('app')->schema());
        self::assertFalse($provider->definition('app')->isRequired());
        self::assertFalse($provider->definition('app')->isWritable());
        self::assertFalse($provider->definition('app')->shouldMergeDefaults());
    }

    public function testMissingDefinitionThrows(): void
    {
        $this->expectException(ConfigNotFoundException::class);

        $this->provider()->definition('missing');
    }

    public function testRegisterDriverMapsFormatsAndPassesConstructorOptions(): void
    {
        $provider = $this->provider();

        $result = $provider->registerDriver('.MEM', MemoryConfigDriver::class, ['suffix' => "\n"]);
        $provider->registerFormat('yml', MemoryConfigDriver::class);
        $provider->registerDriver('mem', MemoryConfigDriver::class);

        self::assertSame($provider, $result);
        self::assertTrue($provider->hasDriverFor('mem'));
        self::assertTrue($provider->hasDriverFor('yaml'));
        self::assertSame(['mem', 'yaml'], $provider->formats());
        self::assertInstanceOf(MemoryConfigDriver::class, $provider->driver('mem'));
        self::assertSame("\n", $provider->driver('mem')->suffix());
    }

    public function testRegisterDriverRejectsInvalidDriversAndDuplicateFormats(): void
    {
        $provider = $this->provider();

        try {
            $provider->registerDriver('bad', \stdClass::class);
            self::fail('Invalid driver class should fail.');
        } catch (ConfigDriverException) {
            self::assertFalse($provider->hasDriverFor('bad'));
        }

        $provider->registerDriver('mem', MemoryConfigDriver::class);

        $this->expectException(ConfigDriverException::class);

        $provider->registerDriver('mem', AlternateConfigDriver::class);
    }

    public function testDriverThrowsForUnsupportedFormats(): void
    {
        $this->expectException(UnsupportedConfigFormatException::class);

        $this->provider()->driver('mem');
    }

    public function testLoadReadsRelativeFileMergesDefaultsValidatesSchemaAndStoresRepositoryValue(): void
    {
        $provider = $this->provider();
        $provider->registerDriver('mem', MemoryConfigDriver::class);
        file_put_contents($this->temporaryPath('app.mem'), '{"name":"demo","database":{"host":"localhost"}}');

        $provider->define('app', ConfigDefinition::file(
            'app.mem',
            null,
            [
                'debug' => false,
                'database' => [
                    'port' => 3306,
                ],
            ],
            new FixtureSchema([
                'name' => 'required|string',
                'database.host' => 'required|string',
                'database.port' => 'required|integer',
            ]),
        ));

        $config = $provider->load('app');

        self::assertSame([
            'debug' => false,
            'database' => [
                'port' => 3306,
                'host' => 'localhost',
            ],
            'name' => 'demo',
        ], $config);
        self::assertSame('demo', $provider->get('app.name'));
        self::assertSame($config, $provider->repository()->get('app'));
    }

    public function testLoadReadsAbsoluteFileAndCanUseExplicitFormat(): void
    {
        $provider = $this->provider();
        $provider->registerDriver('mem', MemoryConfigDriver::class);
        $path = $this->temporaryPath('absolute.noextension');
        file_put_contents($path, '{"name":"demo"}');

        $provider->define('app', ConfigDefinition::file($path, 'mem'));

        self::assertSame(['name' => 'demo'], $provider->load('app'));
    }

    public function testLoadUsesDefaultsWhenOptionalFileIsMissing(): void
    {
        $provider = $this->provider();
        $provider->define('app', ConfigDefinition::file('missing', null, ['name' => 'demo'])->optional());

        self::assertSame(['name' => 'demo'], $provider->load('app'));
    }

    public function testRequiredMissingFileThrowsReadException(): void
    {
        $provider = $this->provider();
        $provider->registerDriver('mem', MemoryConfigDriver::class);
        $provider->define('app', ConfigDefinition::file('missing.mem'));

        $this->expectException(ConfigReadException::class);

        $provider->load('app');
    }

    public function testLoadThrowsWhenSchemaValidationFails(): void
    {
        $provider = $this->provider();
        $provider->registerDriver('mem', MemoryConfigDriver::class);
        file_put_contents($this->temporaryPath('app.mem'), '{"name":123}');
        $provider->define('app', ConfigDefinition::file(
            'app.mem',
            null,
            [],
            new FixtureSchema(['name' => 'required|string']),
        ));

        $this->expectException(ConfigValidationException::class);

        $provider->load('app');
    }

    public function testLoadAllLoadsEveryDefinition(): void
    {
        $provider = $this->provider();
        $provider->registerDriver('mem', MemoryConfigDriver::class);
        file_put_contents($this->temporaryPath('app.mem'), '{"name":"demo"}');
        file_put_contents($this->temporaryPath('database.mem'), '{"host":"localhost"}');
        $provider->define('app', ConfigDefinition::file('app.mem'));
        $provider->define('database', ConfigDefinition::file('database.mem'));

        $repository = $provider->loadAll();

        self::assertSame([
            'app' => ['name' => 'demo'],
            'database' => ['host' => 'localhost'],
        ], $repository->all());
    }

    public function testWriteUsesProvidedConfigAndUpdatesRepository(): void
    {
        $provider = $this->provider();
        $provider->registerDriver('mem', MemoryConfigDriver::class);
        $provider->define('app', ConfigDefinition::file('app.mem', 'mem'));

        $provider->write('app', ['name' => 'demo']);

        self::assertSame('{"name":"demo"}', file_get_contents($this->temporaryPath('app.mem')));
        self::assertSame(['name' => 'demo'], $provider->repository()->get('app'));
    }

    public function testWriteUsesRepositoryWhenNoConfigIsProvided(): void
    {
        $repository = new ConfigRepository(['app' => ['name' => 'demo']]);
        $provider = $this->provider($repository);
        $provider->registerDriver('mem', MemoryConfigDriver::class);
        $provider->define('app', ConfigDefinition::file('app.mem', 'mem'));

        $provider->write('app');

        self::assertSame('{"name":"demo"}', file_get_contents($this->temporaryPath('app.mem')));
    }

    public function testWriteRejectsReadonlyDefinitionsNonArrayRepositoryValuesAndInvalidSchemas(): void
    {
        $provider = $this->provider(new ConfigRepository(['app' => 'not array']));
        $provider->registerDriver('mem', MemoryConfigDriver::class);
        $provider->define('readonly', ConfigDefinition::file('readonly.mem', 'mem')->writable(false));

        try {
            $provider->write('readonly', ['name' => 'demo']);
            self::fail('Readonly definitions should not be writable.');
        } catch (ConfigWriteException) {
            self::assertTrue(true);
        }

        $provider->define('app', ConfigDefinition::file('app.mem', 'mem'));

        try {
            $provider->write('app');
            self::fail('Non-array repository values should not be writable.');
        } catch (ConfigValidationException) {
            self::assertTrue(true);
        }

        $provider->define('schema', ConfigDefinition::file(
            'schema.mem',
            'mem',
            [],
            new FixtureSchema(['name' => 'required|string']),
        ));

        $this->expectException(ConfigValidationException::class);

        $provider->write('schema', ['name' => 123]);
    }

    public function testWriteThrowsWhenRepositoryKeyIsMissing(): void
    {
        $provider = $this->provider();
        $provider->registerDriver('mem', MemoryConfigDriver::class);
        $provider->define('app', ConfigDefinition::file('app.mem', 'mem'));

        $this->expectException(ConfigNotFoundException::class);

        $provider->write('app');
    }

    private function provider(?ConfigRepository $repository = null): ConfigProvider
    {
        return new ConfigProvider(new NativePathResolver($this->temporaryDirectory), $repository);
    }
}
