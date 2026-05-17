# Getting Started

CommonPHP Config stores structured configuration arrays and loads them through format drivers. The core package defines the contracts and access layer. Driver packages such as JSON, PHP, INI, XML, and YAML provide the actual encoding and decoding.

## Install

```bash
composer require comphp/config
```

In this monorepo, the package is also available through the workspace path repository and the root Composer autoloader.

## Use A Repository

`ConfigRepository` is the smallest useful entry point when configuration has already been loaded.

```php
use CommonPHP\Config\ConfigRepository;

$config = new ConfigRepository([
    'app' => [
        'name' => 'Demo',
        'debug' => false,
    ],
]);

$name = $config->get('app.name');
$debug = $config->value('app.debug')->asBool();
```

Dot notation reads nested arrays. A literal top-level key is checked before dot notation, so a key named `app.name` can still be read directly.

## Load A File

`ConfigLoader` is a small driver registry for direct file loading.

```php
use CommonPHP\Config\ConfigLoader;
use CommonPHP\Drivers\Config\JSON\JsonConfigurationDriver;

$loader = new ConfigLoader();
$loader->registerDriver('json', new JsonConfigurationDriver());

$config = $loader->load(__DIR__ . '/config/app.json');
```

The loader infers the format from the file extension unless a format is passed explicitly.

## Define Application Config

`ConfigProvider` is the higher-level application entry point. It uses runtime path resolution and stores loaded config in a repository.

```php
use CommonPHP\Config\ConfigDefinition;
use CommonPHP\Config\ConfigProvider;
use CommonPHP\Drivers\Config\JSON\JsonConfigurationDriver;
use CommonPHP\Runtime\Support\NativePathResolver;

$provider = new ConfigProvider(new NativePathResolver(__DIR__));
$provider->registerDriver('json', JsonConfigurationDriver::class);

$provider->define('app', ConfigDefinition::file(
    'config/app.json',
    defaults: ['debug' => false],
));

$app = $provider->load('app');
$name = $provider->get('app.name');
```

Definitions are loaded into the provider repository under their definition key.
