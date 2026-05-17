<?php

declare(strict_types=1);

namespace CommonPHP\Config\Tests\Unit;

use CommonPHP\Config\Exceptions\ConfigException;
use CommonPHP\Config\Exceptions\ConfigReadException;
use CommonPHP\Config\Exceptions\ConfigValidationException;
use CommonPHP\Config\Exceptions\ConfigWriteException;
use CommonPHP\Config\Tests\Fixtures\MemoryConfigDriver;
use CommonPHP\Config\Tests\Support\TemporaryDirectoryTrait;
use PHPUnit\Framework\TestCase;

final class AbstractConfigDriverTest extends TestCase
{
    use TemporaryDirectoryTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTemporaryDirectory('comphp_config_driver_');
    }

    protected function tearDown(): void
    {
        $this->removeTemporaryDirectory();

        parent::tearDown();
    }

    public function testGetNameDefaultsToConcreteClassName(): void
    {
        self::assertSame(MemoryConfigDriver::class, (new MemoryConfigDriver())->getName());
    }

    public function testValidateEncodeAndDecodeRoundTripArrays(): void
    {
        $driver = new MemoryConfigDriver();
        $config = ['name' => 'demo', 'debug' => true];

        $encoded = $driver->encode($config);

        self::assertTrue($driver->validate($encoded));
        self::assertFalse($driver->validate('{invalid'));
        self::assertSame($config, $driver->decode($encoded));
    }

    public function testDecodeRejectsInvalidAndScalarData(): void
    {
        $driver = new MemoryConfigDriver();

        foreach (['{invalid', '"scalar"'] as $data) {
            try {
                $driver->decode($data);
                self::fail('Invalid fixture data should not decode.');
            } catch (ConfigValidationException) {
                self::assertTrue(true);
            }
        }
    }

    public function testEncodeFailureIsSurfacedAsConfigException(): void
    {
        $this->expectException(ConfigException::class);

        (new MemoryConfigDriver())->encode(['fail_encode' => true]);
    }

    public function testReadReturnsDecodedArrayFromFile(): void
    {
        $path = $this->temporaryPath('config.mem');
        file_put_contents($path, '{"name":"demo"}');

        self::assertSame(['name' => 'demo'], (new MemoryConfigDriver())->read($path));
    }

    public function testReadThrowsForMissingFileDirectoryAndUnreadableFile(): void
    {
        $driver = new MemoryConfigDriver();

        foreach ([$this->temporaryPath('missing.mem'), $this->temporaryDirectory] as $path) {
            try {
                $driver->read($path);
                self::fail('Unreadable path should fail: ' . $path);
            } catch (ConfigReadException) {
                self::assertTrue(true);
            }
        }

        if (PHP_OS_FAMILY === 'Windows') {
            self::markTestSkipped('File readability permissions are not enforced consistently on Windows.');
        }

        $path = $this->temporaryPath('unreadable.mem');
        file_put_contents($path, '{"name":"demo"}');
        chmod($path, 0000);

        try {
            if (is_readable($path)) {
                self::markTestSkipped('The current runtime can still read the chmod-protected file.');
            }

            $this->expectException(ConfigReadException::class);

            $driver->read($path);
        } finally {
            chmod($path, 0600);
        }
    }

    public function testWriteCreatesFilesAndUsesEncodedData(): void
    {
        $path = $this->temporaryPath('config.mem');

        (new MemoryConfigDriver())->write($path, ['name' => 'demo']);

        self::assertFileExists($path);
        self::assertSame('{"name":"demo"}', file_get_contents($path));
    }

    public function testWriteThrowsWhenParentDirectoryIsMissingOrTargetIsDirectory(): void
    {
        $driver = new MemoryConfigDriver();

        foreach ([$this->temporaryPath('missing/config.mem'), $this->temporaryDirectory] as $path) {
            try {
                $driver->write($path, ['name' => 'demo']);
                self::fail('Unwritable path should fail: ' . $path);
            } catch (ConfigWriteException) {
                self::assertTrue(true);
            }
        }
    }

    public function testWriteThrowsForReadonlyFile(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            self::markTestSkipped('File writability permissions are not enforced consistently on Windows.');
        }

        $path = $this->temporaryPath('readonly.mem');
        file_put_contents($path, '{"name":"old"}');
        chmod($path, 0444);

        try {
            if (is_writable($path)) {
                self::markTestSkipped('The current runtime can still write to the chmod-protected file.');
            }

            $this->expectException(ConfigWriteException::class);

            (new MemoryConfigDriver())->write($path, ['name' => 'demo']);
        } finally {
            chmod($path, 0600);
        }
    }

    public function testWritePropagatesEncodeFailures(): void
    {
        $this->expectException(ConfigException::class);

        (new MemoryConfigDriver())->write($this->temporaryPath('config.mem'), ['fail_encode' => true]);
    }
}
