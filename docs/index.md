# CommonPHP Config Documentation

CommonPHP Config is the structured configuration package for CommonPHP applications and standalone PHP projects. It owns config repositories, typed value access, file loading through format drivers, provider-managed config definitions, and lightweight schema validation.

Runtime is responsible for bootstrapping the application and loading the minimal environment needed to start. Config is responsible for the application settings that are safe to load after runtime is available.

## Start Here

- [Getting started](getting-started.md)
- [Usage](usage.md)
- [Package boundaries](package-boundaries.md)

## Config Concepts

- [Repositories](repositories.md)
- [Values](values.md)
- [Loaders and drivers](loaders-and-drivers.md)
- [Definitions and providers](definitions-and-providers.md)
- [Schemas](schemas.md)
- [Error handling](error-handling.md)

## Examples

- [Examples index](examples/index.md)
- [Loading a file](examples/loading-file.md)
- [Provider with schema validation](examples/provider-with-schema.md)

## Development

- [Testing and QA](testing.md)

## Public API Map

Core classes:

- `CommonPHP\Config\ConfigDefinition`
- `CommonPHP\Config\ConfigLoader`
- `CommonPHP\Config\ConfigProvider`
- `CommonPHP\Config\ConfigRepository`
- `CommonPHP\Config\ConfigSchemaValidator`
- `CommonPHP\Config\ConfigValue`

Contracts:

- `CommonPHP\Config\Contracts\ConfigDriverInterface`
- `CommonPHP\Config\Contracts\ConfigProviderInterface`
- `CommonPHP\Config\Contracts\ConfigRepositoryInterface`
- `CommonPHP\Config\Contracts\ConfigSchemaInterface`
- `CommonPHP\Config\Contracts\AbstractConfigDriver`
- `CommonPHP\Config\Contracts\AbstractConfigSchema`

Exceptions:

- `CommonPHP\Config\Exceptions\ConfigException`
- `CommonPHP\Config\Exceptions\ConfigDriverException`
- `CommonPHP\Config\Exceptions\ConfigNotFoundException`
- `CommonPHP\Config\Exceptions\ConfigParseException`
- `CommonPHP\Config\Exceptions\ConfigReadException`
- `CommonPHP\Config\Exceptions\ConfigSchemaException`
- `CommonPHP\Config\Exceptions\ConfigValidationException`
- `CommonPHP\Config\Exceptions\ConfigWriteException`
- `CommonPHP\Config\Exceptions\UnsupportedConfigFormatException`
