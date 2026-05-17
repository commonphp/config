# Values

`ConfigValue` wraps a single repository value and provides typed accessors. It is useful near application boundaries where raw configuration values need to become explicit PHP types.

Related pages:

- [Repositories](repositories.md)
- [Schemas](schemas.md)

## Creating Values

Most callers get values from a repository:

```php
$debug = $repository->value('app.debug')->asBool();
```

Values can also be created directly:

```php
use CommonPHP\Config\ConfigValue;

$found = ConfigValue::found('app.debug', true);
$missing = ConfigValue::missing('app.debug', false);
```

## Presence

```php
$value->exists();
$value->key();
$value->raw();
$value->get();
$value->get('override');
```

For missing values, `get()` returns the default stored in the value wrapper. Passing a default to `get()` overrides the stored default for that call.

## Typed Access

```php
$value->asString();
$value->asInt();
$value->asFloat();
$value->asBool();
$value->asArray();
```

Accessors return `null` when the resolved value is `null`. They throw `ConfigValidationException` when the value cannot be converted safely.

Examples:

```php
$repository->value('mail.port')->asInt();
$repository->value('mail.secure')->asBool(false);
$repository->value('features')->asArray([]);
```

Boolean conversion accepts booleans, integers, and common boolean strings supported by PHP's `FILTER_VALIDATE_BOOLEAN`, such as `true`, `false`, `yes`, `no`, `on`, and `off`.

## String Casting

Casting a `ConfigValue` to string is forgiving:

- `null` becomes an empty string.
- scalar values become their string representation.
- arrays and JSON-encodable values become JSON.
- unencodable values fall back to their debug type.

Prefer typed accessors when correctness matters.
