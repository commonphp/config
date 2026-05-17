# CommonPHP Config

CommonPHP Config provides configuration loading, encoding, decoding, validation, and access patterns for CommonPHP applications. It defines the common configuration driver contract used by format-specific packages such as JSON, PHP, INI, XML, and YAML drivers.

The package is intended to manage structured application configuration after runtime has loaded the minimal environment values needed to boot.

## Requirements

- PHP `^8.5`
- `comphp/runtime:^0.3`

## Installation

Once this package is available through your Composer repositories, install it with:

```bash
composer require comphp/config
```

## Usage

```php
<?php

// TODO: Write usage
```

## Package Notes

This package should own real application configuration, including driver-based loading, encoding, decoding, validation, and optional schema support. Runtime only loads dotenv enough to start the application.

## Error Handling

Read, write, parse, validation, and unsupported format failures should throw CommonPHP config exceptions such as `ConfigReadException`, `ConfigWriteException`, `ConfigValidationException`, or `ConfigException`.

## Documentation

- [Documentation index](docs/index.md)
- [Usage](docs/usage.md)
- [Testing](TESTING.md)
- [Contributing](CONTRIBUTING.md)
- [Security](SECURITY.md)

## License

MIT. See [LICENSE.md](LICENSE.md).
