# comphp/config

## Overview
The `comphp/config` library is designed to simplify the management of configuration files in PHP applications. It leverages dynamic driver loading and custom behaviors to provide a highly adaptable solution for application configuration.

## Features
- **Supports Multiple Formats**: JSON and PHP configurations out of the box.
- **Modular & Extensible**: Easily register additional parsers.
- **Dependency Injection Ready**: Works seamlessly with PSR-11 containers.
- **Merge Strategies**: Replace, merge, or ignore existing configuration values.
- **Dot-Notation Access**: Retrieve nested configuration values effortlessly.

## Installation
Install via Composer:

```sh
composer require comphp/config
```

## Usage
### Initialize ConfigManager
```php
use Neuron\Configuration\ConfigManager;
use DI\ContainerBuilder;

$container = (new ContainerBuilder())->build();
$configManager = new ConfigManager($container);
```

### Load Configuration Files
```php
$configManager->load('database', 'config.json');
$configManager->load('app', 'config.php');
```

### Access Configuration Values
```php
echo $configManager->get('database.host', 'default_host');
```

### Modify Configuration
```php
$configManager->set('cache.enabled', true);
$configManager->unset('cache.enabled');
```

### Convert Configuration to Array
```php
print_r($configManager->toArray());
```

## Supported Formats
By default, `comphp/config` supports:
- JSON (`.json`)
- PHP (`.php` returning an array)

You can register additional parsers using `ParserRegistry`.

## Extending with Custom Parsers
```php
use Neuron\Configuration\Parsers\JsonParser;
use Neuron\Configuration\ParserRegistry;

$parserRegistry = $configManager->parsers;
$parserRegistry->register(YamlParser::class, 'yaml');
$parserRegistry->register(DatabaseParser::class, 'db')
```

## Merge Strategies
When loading configurations, you can define how new values are merged:
- `MergeMode::Replace` (default): Overwrites existing values.
- `MergeMode::Merge`: Merges arrays recursively.
- `MergeMode::Ignore`: Keeps existing values untouched.
- `MergeMode::Error`: Throws an exception if a key already exists.

Example:
```php
use Neuron\Configuration\MergeMode;
$configManager->load('app', 'config.json', MergeMode::Merge);
```

## License
This project is licensed under the MIT License - see the [LICENSE](license.md) file for details.

## Contributing
See [CONTRIBUTING.md](contributing.md) for guidelines on how to contribute.

## Changelog
See [CHANGELOG.md](changelog.md) for a history of updates.

