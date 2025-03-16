<?php

namespace NeuronTests\Configuration;

use DI\ContainerBuilder;
use Neuron\Configuration\Exceptions\UnsupportedSourceException;
use Neuron\Configuration\ParserRegistry;
use Neuron\Configuration\Parsers\JsonParser;
use Neuron\Logging\LogManager;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use function DI\autowire;

class ParserRegistryTest extends TestCase
{
    private ParserRegistry $parserRegistry;
    private ContainerInterface $container;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder()->addDefinitions([
            LoggerInterface::class => autowire(LogManager::class),
        ])->build();
        $this->parserRegistry = $this->container->get(ParserRegistry::class);
    }

    public function testRegisterAndResolveJsonParser(): void
    {
        $this->parserRegistry->register(JsonParser::class, 'json');
        $source = 'config.json';
        $parser = $this->parserRegistry->resolve($source);
        $this->assertInstanceOf(JsonParser::class, $parser);
    }

    public function testResolveUnsupportedSourceThrowsException(): void
    {
        $this->expectException(UnsupportedSourceException::class);
        $source = 'config.yaml';
        $parser = $this->parserRegistry->resolve($source);
    }
}