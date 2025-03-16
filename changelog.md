# Changelog

All notable changes to this project will be documented in this file.

## [0.2.0] - 2025-03-15

### Added
- Initial release of `comphp/config`, a modular and extensible configuration management library.
- Support for **JSON** and **PHP** configuration file parsing.
- **Dot-notation access** for nested configuration values.
- **Merge strategies** (`Replace`, `Merge`, `Ignore`, `Error`) for handling configuration overrides.
- **ParserRegistry** for dynamic parser registration, supporting both **prefix-based** and **suffix-based** resolution.
- **Exception handling** for missing files, invalid formats, and duplicate parsers.
- **PSR-11 compatibility** with dependency injection container integration.
- **Unit tests** covering core functionality.
- **Comprehensive documentation** including examples and setup guide.