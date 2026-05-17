# Definitions And Providers

`ConfigDefinition` describes one named configuration file. `ConfigProvider` manages definitions, drivers, path resolution, defaults, schemas, loading, writing, and repository access.

Related pages:

- [Loaders and drivers](loaders-and-drivers.md)
- [Schemas](schemas.md)
- [Repositories](repositories.md)

## Definitions

```php
use CommonPHP\Config\ConfigDefinition;

$definition = ConfigDefinition::file(
    path: 'config/app.json',
    format: 'json',
    defaults: ['debug' => false],
);
```

A definition stores:

- path;
- optional format;
- defaults;
- optional schema;
- required or optional file behavior;
- writable or read-only behavior;
- default merge behavior.

## Immutable Modifiers

Definitions are immutable. Modifier methods return a new definition.

```php
$definition = ConfigDefinition::file('config/app.json')
    ->withFormat('json')
    ->withDefaults(['debug' => false])
    ->optional()
    ->writable(false);
```

`required()` and `optional()` control missing-file behavior. `writable(false)` prevents provider writes.

## Providers

```php
use CommonPHP\Config\ConfigProvider;
use CommonPHP\Drivers\Config\JSON\JsonConfigurationDriver;
use CommonPHP\Runtime\Support\NativePathResolver;

$provider = new ConfigProvider(new NativePathResolver($root));
$provider->registerDriver('json', JsonConfigurationDriver::class);
$provider->define('app', ConfigDefinition::file('config/app.json'));
```

Drivers are registered by class name because the provider uses runtime's driver pool. Constructor options can be passed as the third argument:

```php
$provider->registerDriver('json', JsonConfigurationDriver::class, [
    'option' => 'value',
]);
```

## Loading

```php
$appConfig = $provider->load('app');
$repository = $provider->loadAll();
```

Loaded config is stored under the definition key:

```php
$provider->get('app.name');
$provider->repository()->get('app.name');
```

Defaults are merged before schema validation. Associative arrays merge recursively and list arrays are replaced.

## Writing

```php
$provider->write('app', ['name' => 'Demo']);

$provider->repository()->set('app.name', 'Demo');
$provider->write('app');
```

The provider validates schema-backed definitions before writing.

## Paths

Relative definition paths are resolved with the configured `PathResolverInterface`. Absolute paths are used as-is.

This keeps config definitions portable inside applications while still allowing tests and tools to use temporary absolute paths.
