# Usage

The package has three common usage styles: direct repository access, direct file loading, and provider-managed application configuration.

## Repository Access

Use a repository when the caller already has an array.

```php
use CommonPHP\Config\ConfigRepository;

$repository = new ConfigRepository([
    'mail' => [
        'host' => 'smtp.example.test',
        'port' => 587,
    ],
]);

$host = $repository->get('mail.host');
$port = $repository->value('mail.port')->asInt();
```

`get()` returns a default for missing values. `getRequired()` throws `ConfigNotFoundException`.

```php
$host = $repository->get('mail.host', 'localhost');
$required = $repository->getRequired('mail.host');
```

## Loading Files

Use `ConfigLoader` when an application or tool wants to read or write files directly.

```php
use CommonPHP\Config\ConfigLoader;
use CommonPHP\Drivers\Config\PHP\PhpConfigurationDriver;

$loader = new ConfigLoader();
$loader->registerDriver('php', new PhpConfigurationDriver());

$config = $loader->read(__DIR__ . '/config/app.php');

$loader->write(__DIR__ . '/config/cache.php', [
    'enabled' => true,
]);
```

`read()` is an alias of `load()`. `save()` is an alias of `write()`.

## Provider-Managed Config

Use `ConfigProvider` when configuration should be named, path-resolved, defaulted, validated, and stored in a shared repository.

```php
$provider
    ->registerDriver('php', PhpConfigurationDriver::class)
    ->defineFile('app', 'config/app.php', defaults: [
        'debug' => false,
    ]);

$provider->loadAll();

$debug = $provider->get('app.debug', false);
```

`defineFile()` is a convenience wrapper around `define()` and `ConfigDefinition::file()`.

## Writing Through A Provider

Provider writes use the same definition path and driver that reads use.

```php
$provider->write('app', [
    'debug' => true,
]);
```

When no array is passed, the provider writes the current repository value for that definition key.

```php
$provider->repository()->set('app.debug', true);
$provider->write('app');
```
