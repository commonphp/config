# Changelog

All notable changes to this project will be documented in this file.

## [0.2.0] - 2025-03-15

### Added
- Refactored `EventDispatcher` to improve efficiency and align with PSR-14.
- Introduced `ListenerInterface` for structured event handling.
- Added support for prioritized listeners using `SplPriorityQueue`.
- Implemented `StoppableEventInterface` for event propagation control.
- Improved exception handling with `InvalidEventException` and `InvalidListenerException`.

