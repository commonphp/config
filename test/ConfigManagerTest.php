<?php

namespace NeuronTests\Configuration;

use DI\ContainerBuilder;
use Neuron\Configuration\ConfigManager;
use Neuron\Configuration\Exceptions\FileMissingException;
use Neuron\Configuration\Exceptions\UnsupportedSourceException;
use Neuron\Configuration\ParserRegistry;
use Neuron\Configuration\Parsers\JsonParser;
use Neuron\Configuration\Parsers\PhpParser;
use Neuron\Logging\LogManager;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use function DI\autowire;

class ConfigManagerTest extends TestCase
{
    private ConfigManager $configManager;
    private ParserRegistry $parserRegistry;
    private ContainerInterface $container;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder()->addDefinitions([
            LoggerInterface::class => autowire(LogManager::class),
        ])->build();
        $this->parserRegistry = $this->container->get(ParserRegistry::class);
        $this->parserRegistry->register(JsonParser::class, 'json');
        $this->parserRegistry->register(PhpParser::class, 'php');

        $this->configManager = new ConfigManager($this->container);
    }

    public function testLoadJsonConfig(): void
    {
        $file = __DIR__ . '/config.json';
        file_put_contents($file, json_encode(['db' => ['host' => 'localhost']]))
        ;
        $this->configManager->load('database', $file);
        $this->assertEquals('localhost', $this->configManager['database']['db']['host']);
        unlink($file);
    }

    public function testLoadPhpConfig(): void
    {
        $file = __DIR__ . '/config.php';
        file_put_contents($file, '<?php return ["db" => ["host" => "127.0.0.1"]];');
        $this->configManager->load('database', $file);
        $this->assertEquals('127.0.0.1', $this->configManager['database']['db']['host']);
        unlink($file);
    }

    public function testLoadMissingFileThrowsException(): void
    {
        $this->expectException(FileMissingException::class);
        $this->configManager->load('database', __DIR__ . '/missing.json');
    }

    public function testLoadUnsupportedFormatThrowsException(): void
    {
        $this->expectException(UnsupportedSourceException::class);
        $file = __DIR__ . '/config.xml';
        file_put_contents($file, '<config></config>');
        $this->configManager->load('database', $file);
        unlink($file);
    }
}
