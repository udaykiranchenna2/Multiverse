# Changelog

All notable changes to `madeiteasytools/multiverse` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Planned

- Node.js driver support
- Go language support
- Worker events (WorkerExecuting, WorkerExecuted, WorkerFailed)
- Performance monitoring dashboard
- Worker versioning system
- Async worker execution
- Worker output streaming

---

## [1.0.0] - 2026-02-04

### Added

- Initial release of MadeItEasyTools/Multiverse
- Python 3.8+ support with shared virtual environments
- Core `WorkerManager` class for executing workers
- `MultiWorker` facade for convenient access
- Security scanning with configurable dangerous pattern detection
- Path traversal protection for worker names
- Artisan commands:
    - `multiverse:install` - Setup language environments
    - `multiverse:update` - Update language dependencies
    - `make:worker` - Generate new worker files
    - `worker:run` - Execute workers manually
- Configuration file (`config/multiverse.php`) with:
    - Customizable workers directory path
    - Language driver mappings
    - Python-specific settings (venv path, requirements path)
    - Security settings (scan toggle, dangerous patterns)
- `PythonDriver` with:
    - Shared virtual environment support
    - Automatic dependency management
    - Static code analysis before execution
- Comprehensive documentation:
    - README.md - Quick start guide
    - DOCUMENTATION.md - Complete user guide
    - API.md - API reference
    - EXAMPLES.md - Real-world examples
    - CHANGELOG.md - Version history
- PHPDoc documentation for all public methods
- Example workers:
    - `glitch_art` - Image glitch effects using NumPy
    - `privacy_guard` - Face detection and anonymization using OpenCV
- Security features:
    - Banned pattern detection (`rm -rf`, `shutil.rmtree`, `mkfs`, etc.)
    - Worker name validation (alphanumeric, dashes, underscores only)
    - Configurable security rules

### Security

- Implemented static code analysis to prevent execution of dangerous commands
- Added path traversal protection
- Validated worker names to prevent directory traversal attacks
- Scans Python files before execution for banned patterns

---

## Development Notes

### Version Numbering

- **Major** (X.0.0): Breaking changes, major new features
- **Minor** (0.X.0): New features, backward compatible
- **Patch** (0.0.X): Bug fixes, minor improvements

### Breaking Changes Policy

Breaking changes will only be introduced in major versions and will be clearly documented with migration guides.

---

## Upgrade Guide

### From 0.x to 1.0.0

This is the initial stable release. No upgrade path needed.

---

## Contributors

- Uday Kiran Chenna - Initial development

---

## License

MIT License - See LICENSE file for details
