<?php

namespace NeuronTests\Configuration;

use Neuron\Configuration\Exceptions\FormatException;
use Neuron\Configuration\Parsers\JsonParser;
use PHPUnit\Framework\TestCase;

class JsonParserTest extends TestCase
{
    private JsonParser $parser;

    protected function setUp(): void
    {
        $this->parser = new JsonParser();
    }

    public function testDeserializeValidJson(): void
    {
        $data = '{"db": {"host": "localhost"}}';
        $result = $this->parser->deserialize($data);
        $this->assertEquals(['db' => ['host' => 'localhost']], $result);
    }

    public function testDeserializeInvalidJsonThrowsException(): void
    {
        $this->expectException(FormatException::class);
        $this->parser->deserialize('{invalid json}');
    }
}