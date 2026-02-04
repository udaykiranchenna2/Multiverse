# Contributing to MadeItEasyTools/Multiverse

Thank you for considering contributing to MadeItEasyTools/Multiverse! This document provides guidelines and instructions for contributing.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [How Can I Contribute?](#how-can-i-contribute)
- [Development Setup](#development-setup)
- [Coding Standards](#coding-standards)
- [Testing](#testing)
- [Pull Request Process](#pull-request-process)

---

## Code of Conduct

### Our Pledge

We are committed to providing a welcoming and inspiring community for all. Please be respectful and constructive in all interactions.

### Expected Behavior

- Use welcoming and inclusive language
- Be respectful of differing viewpoints
- Accept constructive criticism gracefully
- Focus on what is best for the community

### Unacceptable Behavior

- Harassment or discriminatory language
- Trolling or insulting comments
- Publishing others' private information
- Other conduct which could reasonably be considered inappropriate

---

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check existing issues to avoid duplicates.

**Good Bug Reports Include:**

- Clear, descriptive title
- Steps to reproduce the problem
- Expected vs actual behavior
- Code samples if applicable
- Environment details (PHP version, Laravel version, OS)

**Example:**

````markdown
## Bug: Worker fails with large payloads

**Environment:**

- PHP 8.2
- Laravel 10.x
- Ubuntu 22.04

**Steps to Reproduce:**

1. Create worker with large JSON payload (>1MB)
2. Run `MultiWorker::run('my_worker', $largePayload)`
3. Observe error

**Expected:** Worker executes successfully
**Actual:** RuntimeException: "Broken pipe"

**Code Sample:**

```php
$payload = ['data' => str_repeat('x', 1000000)];
MultiWorker::run('test_worker', $payload);
```
````

````

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues.

**Good Enhancement Suggestions Include:**
- Clear use case
- Why existing features don't solve the problem
- Proposed solution
- Alternative solutions considered

**Example:**
```markdown
## Enhancement: Add Node.js Support

**Use Case:**
I need to run JavaScript workers for server-side rendering.

**Current Limitation:**
Only Python is supported.

**Proposed Solution:**
Add `NodeDriver` class similar to `PythonDriver`.

**Implementation Ideas:**
- Use `npm` for dependency management
- Support `package.json`
- Execute with `node` command
````

### Contributing Code

1. **Fork the repository**
2. **Create a feature branch** (`git checkout -b feature/amazing-feature`)
3. **Make your changes**
4. **Write tests**
5. **Run tests** (`composer test`)
6. **Commit your changes** (`git commit -m 'Add amazing feature'`)
7. **Push to branch** (`git push origin feature/amazing-feature`)
8. **Open a Pull Request**

---

## Development Setup

### Prerequisites

- PHP 8.1+
- Composer
- Python 3.8+ (for testing Python workers)
- Git

### Setup Steps

1. **Clone your fork:**

```bash
git clone https://github.com/YOUR_USERNAME/multiverse.git
cd multiverse
```

2. **Install dependencies:**

```bash
composer install
```

3. **Run tests:**

```bash
composer test
```

4. **Setup Python environment (for testing):**

```bash
php artisan multiverse:install --lang=python
```

### Project Structure

```
packages/MadeItEasyTools/Multiverse/
├── config/
│   └── multiverse.php          # Configuration file
├── src/
│   ├── Commands/               # Artisan commands
│   ├── Contracts/              # Interfaces
│   ├── Drivers/                # Language drivers
│   ├── Facades/                # Laravel facades
│   ├── Process/                # Process execution
│   ├── WorkerManager.php       # Core manager class
│   └── MultiverseServiceProvider.php
├── tests/
│   └── Unit/                   # Unit tests
├── README.md
├── DOCUMENTATION.md
├── API.md
├── EXAMPLES.md
├── CHANGELOG.md
└── composer.json
```

---

## Coding Standards

### PHP Standards

We follow **PSR-12** coding standards and use **strict types**.

#### Key Rules:

- Use `declare(strict_types=1);` in all PHP files
- Type hint all parameters and return types
- Use PHPDoc for complex types and descriptions
- Keep methods focused and small
- Use meaningful variable names

#### Example:

```php
<?php

declare(strict_types=1);

namespace MadeItEasyTools\Multiverse;

/**
 * Example class demonstrating coding standards.
 */
class Example
{
    /**
     * Process data with strict typing.
     *
     * @param array<string, mixed> $data Input data
     * @return array<string, mixed> Processed data
     */
    public function process(array $data): array
    {
        // Implementation
        return $data;
    }
}
```

### Documentation Standards

#### PHPDoc Requirements:

- All public methods must have PHPDoc
- Include `@param`, `@return`, `@throws` tags
- Add `@example` for complex methods
- Use `@var` for class properties

#### Example:

````php
/**
 * Execute a worker with the given payload.
 *
 * @param string $workerName Worker name (alphanumeric, dashes, underscores)
 * @param array<string, mixed> $payload Data to pass to worker
 * @return array<string, mixed> Worker output
 *
 * @throws InvalidArgumentException If worker name invalid
 * @throws RuntimeException If worker not found
 *
 * @example
 * ```php
 * $result = $manager->run('image_processor', [
 *     'image_url' => 'https://example.com/image.jpg'
 * ]);
 * ```
 */
public function run(string $workerName, array $payload = []): array
{
    // Implementation
}
````

### Python Standards

For Python workers, follow **PEP 8**.

#### Key Rules:

- Use 4 spaces for indentation
- Maximum line length: 79 characters
- Use docstrings for functions
- Handle exceptions properly

#### Example:

```python
import sys
import json

def main():
    """
    Main entry point for the worker.
    Reads JSON from stdin and outputs JSON to stdout.
    """
    try:
        data = json.loads(sys.stdin.read())
        result = process_data(data)
        print(json.dumps(result))
    except Exception as e:
        error_result = {"status": "error", "message": str(e)}
        print(json.dumps(error_result))

def process_data(data: dict) -> dict:
    """
    Process the input data.

    Args:
        data: Input data dictionary

    Returns:
        Processed data dictionary
    """
    return {"status": "success", "data": data}

if __name__ == "__main__":
    main()
```

---

## Testing

### Running Tests

```bash
# Run all tests
composer test

# Run specific test
./vendor/bin/phpunit tests/Unit/WorkerManagerTest.php

# Run with coverage
composer test-coverage
```

### Writing Tests

All new features must include tests.

#### Example Test:

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use MadeItEasyTools\Multiverse\WorkerManager;

class WorkerManagerTest extends TestCase
{
    public function test_it_validates_worker_name(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $manager = app(WorkerManager::class);
        $manager->run('../../../etc/passwd', []);
    }

    public function test_it_executes_worker(): void
    {
        $manager = app(WorkerManager::class);

        $result = $manager->run('echo_worker', [
            'message' => 'test'
        ]);

        $this->assertEquals('success', $result['status']);
    }
}
```

---

## Pull Request Process

### Before Submitting

1. **Update documentation** if you changed functionality
2. **Add tests** for new features
3. **Run tests** to ensure nothing broke
4. **Update CHANGELOG.md** with your changes
5. **Follow coding standards**

### PR Template

```markdown
## Description

Brief description of changes

## Type of Change

- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing

- [ ] Tests pass locally
- [ ] Added new tests
- [ ] Manual testing completed

## Checklist

- [ ] Code follows style guidelines
- [ ] Self-review completed
- [ ] Documentation updated
- [ ] CHANGELOG.md updated
```

### Review Process

1. **Automated checks** must pass (tests, linting)
2. **Code review** by maintainer
3. **Requested changes** must be addressed
4. **Approval** from at least one maintainer
5. **Merge** by maintainer

---

## Adding New Language Drivers

To add support for a new language (e.g., Node.js):

### 1. Create Driver Class

```php
<?php

namespace MadeItEasyTools\Multiverse\Drivers;

use MadeItEasyTools\Multiverse\Contracts\LanguageDriver;

class NodeDriver implements LanguageDriver
{
    public function installDependencies(string $workerPath): void
    {
        // Install from package.json
    }

    public function getRunCommand(string $workerPath, string $scriptName): array
    {
        return ['node', $workerPath . '/' . $scriptName];
    }
}
```

### 2. Update Configuration

```php
// config/multiverse.php

'drivers' => [
    'python' => PythonDriver::class,
    'node' => NodeDriver::class, // Add this
],

'node' => [
    'root_path' => 'multiverse/node',
    'package_json_path' => 'multiverse/node/package.json',
],
```

### 3. Update Install Command

Add Node.js installation logic to `InstallLanguageCommand`.

### 4. Write Tests

```php
public function test_node_driver_executes_worker(): void
{
    // Test implementation
}
```

### 5. Update Documentation

- Add to README.md
- Add examples to EXAMPLES.md
- Update API.md

---

## Questions?

- **Issues:** https://github.com/madeiteasytools/multiverse/issues
- **Discussions:** https://github.com/madeiteasytools/multiverse/discussions
- **Email:** support@neuroport.dev

---

Thank you for contributing! 🚀
