# Repositories

`ConfigRepository` is a mutable wrapper around a configuration array. It provides dot-key access, recursive merging, array-style access, iteration, and typed value wrappers.

Related pages:

- [Values](values.md)
- [Usage](usage.md)
- [Error handling](error-handling.md)

## Creating A Repository

```php
use CommonPHP\Config\ConfigRepository;

$repository = new ConfigRepository([
    'app' => [
        'name' => 'Demo',
    ],
]);

$same = ConfigRepository::fromArray(['debug' => true]);
```

## Reading Values

```php
$repository->get('app.name');
$repository->get('app.missing', 'fallback');
$repository->getRequired('app.name');
```

Dot notation traverses nested arrays. The repository checks literal top-level keys first:

```php
$repository = new ConfigRepository([
    'app.name' => 'literal',
    'app' => ['name' => 'nested'],
]);

$repository->get('app.name'); // literal
```

Use an empty key to read the root array.

```php
$all = $repository->get('');
```

## Writing Values

```php
$repository->set('app.name', 'Demo');
$repository->set('mail.host', 'smtp.example.test');
```

Intermediate arrays are created automatically. Setting the root key replaces the entire repository and must receive an array:

```php
$repository->set('', ['app' => ['name' => 'Demo']]);
```

## Merging Values

`merge()` recursively merges associative arrays and replaces list arrays.

```php
$repository->merge([
    'database' => [
        'host' => 'localhost',
        'options' => [
            'ssl' => true,
        ],
    ],
]);
```

## Removing Values

```php
$repository->remove('app.debug');
$repository->clear();
```

Removing a missing key is a no-op. Removing the empty root key clears the repository.

## Array And Iterator Access

```php
$repository['app.debug'] = true;
$debug = $repository['app.debug'];
unset($repository['app.debug']);

foreach ($repository as $key => $value) {
    // Top-level entries.
}
```

`count($repository)` returns the number of top-level keys.
