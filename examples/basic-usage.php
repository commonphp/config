<?php

use Neuron\Configuration\ConfigManager;
use Neuron\Configuration\MergeMode;
use Neuron\Configuration\Parsers\JsonParser;
use Neuron\Configuration\Parsers\PhpParser;
use DI\ContainerBuilder;

// Create a dependency injection container
$containerBuilder = new ContainerBuilder();
$container = $containerBuilder->build();

// Initialize ConfigManager with default parsers
$configManager = new ConfigManager($container);

// Example: Load a JSON configuration file
$configManager->load('database', 'config.json');
echo "Database host: " . $configManager->get('database.host', 'default_host') . "\n";

// Example: Load a PHP configuration file
$configManager->load('app', 'config.php', MergeMode::Merge);
echo "App name: " . $configManager->get('app.name', 'default_app') . "\n";

// Example: Set and retrieve configuration values
$configManager->set('cache.enabled', true);
echo "Cache enabled: " . ($configManager->get('cache.enabled') ? 'Yes' : 'No') . "\n";

// Example: Check if a key exists
if ($configManager->isset('database.host')) {
    echo "Database host is set.\n";
}

// Example: Remove a configuration key
$configManager->unset('cache.enabled');
echo "Cache enabled after unset: " . ($configManager->get('cache.enabled', 'Not Set')) . "\n";

// Example: Convert configuration to array
print_r($configManager->toArray());
