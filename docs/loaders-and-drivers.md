# Loaders And Drivers

`ConfigLoader` reads and writes configuration files through registered format drivers. The loader is intentionally small: it maps format names to driver instances and delegates parsing and encoding.

Related pages:

- [Definitions and providers](definitions-and-providers.md)
- [Package boundaries](package-boundaries.md)
- [Error handling](error-handling.md)

## Driver Contract

Format drivers implement `ConfigDriverInterface`.

```php
namespace CommonPHP\Config\Contracts;

use CommonPHP\Runtime\Contracts\DriverInterface;

interface ConfigDriverInterface extends DriverInterface
{
    public function validate(string $data): bool;
    public function encode(array $config): string;
    public function decode(string $data): array;
    public function read(string $filename): array;
    public function write(string $filename, array $config): void;
}
```

Drivers should throw config exceptions for invalid data, unsupported values, read failures, and write failures.

## AbstractConfigDriver

`AbstractConfigDriver` implements common filesystem read/write behavior. A format driver usually implements only:

- `validate()`
- `encode()`
- `decode()`

It also returns the concrete class name from `getName()`.

## Registering Drivers

```php
use CommonPHP\Config\ConfigLoader;
use CommonPHP\Drivers\Config\JSON\JsonConfigurationDriver;

$loader = new ConfigLoader();
$loader->registerDriver('json', new JsonConfigurationDriver());
```

Format names are normalized:

- leading dots are removed;
- names are lowercased;
- `yml` maps to `yaml`.

## Loading And Writing

```php
$config = $loader->load(__DIR__ . '/app.json');
$config = $loader->read(__DIR__ . '/app.json');

$loader->write(__DIR__ . '/cache.json', ['enabled' => true]);
$loader->save(__DIR__ . '/cache.json', ['enabled' => true]);
```

The file extension is used as the format unless an explicit format is passed.

```php
$config = $loader->load(__DIR__ . '/settings', 'json');
```

## Loading Into A Repository

```php
$loader->loadInto(__DIR__ . '/app.json');
$loader->loadInto(__DIR__ . '/database.json', 'database');
```

When no key is provided, the loaded config is merged into the root repository. When a key is provided, the loaded config is stored at that key.

## Driver Packages

The core config package does not depend on any specific format parser. Format-specific packages provide concrete drivers:

- `comphp/config-json`
- `comphp/config-php`
- `comphp/config-ini`
- `comphp/config-xml`
- `comphp/config-yaml`
