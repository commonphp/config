# Package Boundaries

CommonPHP Config is the configuration access and loading layer. It should stay understandable, debuggable, and easy to update.

## Owned By Config

This package owns:

- configuration repositories;
- typed value access;
- file loading and writing through driver contracts;
- provider-managed config definitions;
- default merging;
- optional schema validation;
- config exception types.

## Owned By Format Drivers

Format-specific packages own parsing and encoding details:

- JSON syntax and JSON encoding options;
- PHP config files returning arrays;
- INI section limitations;
- XML representation;
- YAML parser integration.

The core package should not depend on any one format parser.

## Owned By Runtime

Runtime owns:

- application startup and shutdown;
- the application container;
- path resolver contracts and implementations;
- driver pool infrastructure;
- environment loading needed to boot.

Config uses runtime's path resolver and driver traits, but it does not boot applications.

## Not Owned By Config

Config should not own:

- secret storage;
- database access;
- HTTP request parsing;
- form validation;
- user-input validation flows;
- cache storage;
- module discovery.

Those concerns belong to other packages or application code.

## Design Guidance

Prefer explicit arrays, small definitions, and simple schemas. If a configuration rule starts to feel like business logic, move it into the application service that consumes the configuration.
