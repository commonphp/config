# Testing And QA

The config package has a PHPUnit unit suite under `tests/Unit`.

## Running Tests

From the repository root:

```bash
vendor/bin/phpunit -c package/config/phpunit.xml.dist
```

On Windows:

```bash
vendor\bin\phpunit.bat -c package\config\phpunit.xml.dist
```

## Test Coverage Areas

The suite covers:

- `ConfigDefinition` construction and immutable modifiers;
- `ConfigRepository` dot-key access, merging, mutation, array access, iteration, and missing-key behavior;
- `ConfigValue` typed conversion and string casting;
- `AbstractConfigDriver` filesystem read/write behavior;
- `ConfigLoader` driver registration, format inference, direct reads/writes, and repository loading;
- `ConfigProvider` definitions, driver registration, path resolution, loading, writing, defaults, schemas, and failure modes;
- `ConfigSchemaValidator` string rules, array rules, type aliases, callbacks, allowed values, patterns, and schema objects;
- exception hierarchy.

Some permission tests are skipped on Windows because chmod readability and writability are not enforced consistently there.

## Fixture Drivers

Tests use small fixture drivers in `tests/Fixtures` so core behavior can be tested without depending on a specific external format driver package.

Format driver packages still have their own tests for JSON, PHP, INI, XML, and YAML behavior.
