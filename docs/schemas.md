# Schemas

Schemas provide lightweight structural validation for loaded configuration arrays. They are intentionally smaller than full JSON Schema and are designed for simple application config checks.

Related pages:

- [Definitions and providers](definitions-and-providers.md)
- [Values](values.md)
- [Error handling](error-handling.md)

## Schema Objects

Create a schema by extending `AbstractConfigSchema`.

```php
use CommonPHP\Config\Contracts\AbstractConfigSchema;

final class AppConfigSchema extends AbstractConfigSchema
{
}

$schema = new AppConfigSchema([
    'name' => 'required|string',
    'debug' => 'boolean',
    'database.host' => 'required|string',
    'database.port' => 'required|integer',
]);
```

Schemas can be passed to `ConfigDefinition`.

```php
$provider->define('app', ConfigDefinition::file(
    'config/app.json',
    schema: $schema,
));
```

## Direct Validation

```php
use CommonPHP\Config\ConfigSchemaValidator;

$validator = new ConfigSchemaValidator();
$errors = $validator->validate($config, [
    'name' => 'required|string',
]);

$validator->assertValid($config, [
    'name' => 'required|string',
]);
```

`validate()` returns a list of error messages. `assertValid()` throws `ConfigValidationException` when errors are present.

## Rule Strings

Rules can be compact strings:

```php
[
    'name' => 'required|string',
    'debug' => 'boolean',
    'timezone' => 'nullable|string',
]
```

Supported rule tokens:

- `required`
- `optional`
- `nullable`
- type names such as `string`, `integer`, `boolean`, `array`, `list`, `scalar`, `object`, `callable`, `mixed`, and `null`
- aliases `bool`, `int`, `double`, and `numeric`
- `type:string,integer`
- `in:production,staging`

## Array Definitions

Rules can also be arrays:

```php
[
    'name' => [
        'required' => true,
        'type' => 'string',
        'pattern' => '/^[a-z0-9_-]+$/',
    ],
    'env' => [
        'type' => 'string',
        'allowed' => ['production', 'staging'],
    ],
]
```

Supported array keys:

- `required`
- `nullable`
- `type`
- `types`
- `allowed`
- `enum`
- `pattern`
- `rules`
- `callback`

## Callbacks

Callbacks receive the value, field name, and full config array.

```php
[
    'database.port' => static function (mixed $value): bool {
        return is_int($value) && $value > 0;
    },
]
```

Return `true` or `null` to pass, `false` for a generic error, or a non-empty string for a custom error message.
