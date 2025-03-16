<?php

namespace NeuronTests\Configuration;

use Neuron\Configuration\ConfigCollection;
use PHPUnit\Framework\TestCase;

class ConfigCollectionTest extends TestCase
{
    public function testSetAndGetConfigValues(): void
    {
        $collection = new ConfigCollection();
        $collection->set('app.name', 'TestApp');
        $this->assertEquals('TestApp', $collection->get('app.name'));
    }

    public function testUnsetConfigValue(): void
    {
        $collection = new ConfigCollection(['app' => ['name' => 'TestApp']]);
        $collection->unset('app.name');
        $this->assertNull($collection->get('app.name'));
    }
}