# Error Handling

Config exceptions are rooted at `ConfigException`. Specific exception types describe the failure surface so callers can recover at the right layer.

Related pages:

- [Loaders and drivers](loaders-and-drivers.md)
- [Definitions and providers](definitions-and-providers.md)
- [Schemas](schemas.md)

## Exception Hierarchy

All package exceptions extend `CommonPHP\Config\Exceptions\ConfigException`.

Specific exceptions:

- `ConfigDriverException`
- `ConfigNotFoundException`
- `ConfigParseException`
- `ConfigReadException`
- `ConfigSchemaException`
- `ConfigValidationException`
- `ConfigWriteException`
- `UnsupportedConfigFormatException`

## Missing Values

Repository reads can stay non-throwing:

```php
$debug = $repository->get('app.debug', false);
```

Use `getRequired()` when missing configuration is a setup error:

```php
$dsn = $repository->getRequired('database.dsn');
```

## File Failures

Drivers and loaders throw:

- `ConfigReadException` for missing, non-file, or unreadable files;
- `ConfigWriteException` for unwritable paths or failed writes;
- `ConfigValidationException` for invalid decoded data;
- `UnsupportedConfigFormatException` when no registered driver exists.

## Provider Failures

Providers can throw:

- `ConfigNotFoundException` for unknown definitions or missing repository keys during writes;
- `ConfigDriverException` for invalid driver classes or conflicting format registrations;
- `ConfigReadException` for required missing files;
- `ConfigValidationException` for schema failures or non-array write values;
- `ConfigWriteException` for read-only definitions or file write failures.

## Recommended Application Handling

Treat configuration errors as boot or deployment errors unless the application is intentionally editing configuration at runtime.

```php
try {
    $provider->loadAll();
} catch (ConfigException $e) {
    // Log and stop startup.
}
```
