<?php

namespace NeuronTests\Configuration;

use Neuron\Configuration\Parsers\PhpParser;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class PhpParserTest extends TestCase
{
    private PhpParser $parser;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->parser = new PhpParser($this->logger);
    }

    public function testDeserializePhpConfig(): void
    {
        $file = __DIR__ . '/config.php';
        file_put_contents($file, '<?php return ["db" => ["host" => "localhost"]];');
        $result = $this->parser->read($file);
        $this->assertEquals(['db' => ['host' => 'localhost']], $result);
        unlink($file);
    }
}