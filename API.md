# API Reference

## Table of Contents

- [WorkerManager](#workermanager)
- [Facades](#facades)
- [Commands](#commands)
- [Configuration](#configuration)
- [Drivers](#drivers)

---

## WorkerManager

The core class for executing workers.

### Methods

#### `run(string $workerName, array $payload = []): array`

Execute a worker with the given payload.

**Parameters:**

- `$workerName` (string): Name of the worker (alphanumeric, dashes, underscores only)
- `$payload` (array): Associative array of data to pass to the worker

**Returns:**

- `array`: The worker's JSON output as an associative array

**Throws:**

- `InvalidArgumentException`: If worker name contains invalid characters
- `RuntimeException`: If worker not found or execution fails

**Example:**

```php
use MadeItEasyTools\Multiverse\WorkerManager;

$manager = app(WorkerManager::class);

$result = $manager->run('image_processor', [
    'image_url' => 'https://example.com/image.jpg',
    'filter' => 'blur'
]);

if ($result['status'] === 'success') {
    echo "Output: " . $result['output_path'];
}
```

#### `driver(string $driver): LanguageDriver`

Get or create a language driver instance.

**Parameters:**

- `$driver` (string): Driver name (e.g., 'python', 'node')

**Returns:**

- `LanguageDriver`: The driver instance

**Throws:**

- `InvalidArgumentException`: If driver not configured

---

## Facades

### MultiWorker

Convenient facade for accessing WorkerManager.

**Example:**

```php
use MadeItEasyTools\Multiverse\Facades\MultiWorker;

// Simple usage
$result = MultiWorker::run('echo_worker', ['message' => 'Hello']);

// With error handling
try {
    $result = MultiWorker::run('complex_worker', $data);
} catch (\Exception $e) {
    Log::error('Worker failed: ' . $e->getMessage());
}
```

---

## Commands

### multiverse:install

Setup a language environment.

**Signature:**

```bash
php artisan multiverse:install --lang=python
```

**Options:**

- `--lang=LANGUAGE`: Language to install (required)
    - Supported: `python`, `node` (coming soon)

**What it does:**

1. Creates language directory structure
2. Sets up virtual environment
3. Creates requirements file
4. Installs base dependencies

**Example:**

```bash
php artisan multiverse:install --lang=python
```

---

### multiverse:update

Update language dependencies.

**Signature:**

```bash
php artisan multiverse:update --lang=python
```

**Options:**

- `--lang=LANGUAGE`: Language to update (required)

**What it does:**

1. Reads requirements file
2. Installs/updates packages in virtual environment

**Example:**

```bash
# After adding packages to multiverse/python/requirements.txt
php artisan multiverse:update --lang=python
```

---

### make:worker

Create a new worker.

**Signature:**

```bash
php artisan make:worker {name} --lang=python
```

**Arguments:**

- `name`: Worker name (required)

**Options:**

- `--lang=LANGUAGE`: Language for the worker (optional, prompts if not provided)

**What it does:**

1. Creates worker directory
2. Generates main script file
3. Creates .gitignore

**Example:**

```bash
php artisan make:worker image_processor --lang=python
```

Creates:

```
multiverse/
└── python/
    └── image_processor/
        ├── main.py
        └── .gitignore
```

---

### worker:run

Execute a worker manually.

**Signature:**

```bash
php artisan worker:run {worker} --payload=JSON
```

**Arguments:**

- `worker`: Worker name (required)

**Options:**

- `--payload=JSON`: JSON payload to pass (optional, defaults to `{}`)

**Example:**

```bash
php artisan worker:run image_processor --payload='{"image_url": "https://example.com/img.jpg"}'
```

---

## Configuration

### config/multiverse.php

#### `workers_path`

**Type:** `string`  
**Default:** `base_path('multiverse')`

Root directory for all workers.

```php
'workers_path' => base_path('multiverse'),
```

---

#### `drivers`

**Type:** `array<string, string>`  
**Default:** `['python' => PythonDriver::class]`

Mapping of language names to driver classes.

```php
'drivers' => [
    'python' => \MadeItEasyTools\Multiverse\Drivers\PythonDriver::class,
    'node' => \MadeItEasyTools\Multiverse\Drivers\NodeDriver::class,
],
```

---

#### `python`

**Type:** `array`

Python-specific configuration.

```php
'python' => [
    'root_path' => 'multiverse/python',
    'venv_path' => 'multiverse/python/venv',
    'requirements_path' => 'multiverse/python/requirements.txt',
],
```

**Keys:**

- `root_path`: Python workers directory
- `venv_path`: Virtual environment location
- `requirements_path`: Requirements file location

---

#### `security`

**Type:** `array`

Security scanning configuration.

```php
'security' => [
    'scan_for_dangerous_code' => true,
    'dangerous_patterns' => [
        'rm -rf' => 'destructive deletion detected',
        'shutil.rmtree' => 'directory deletion detected',
    ],
],
```

**Keys:**

- `scan_for_dangerous_code` (bool): Enable/disable security scanning
- `dangerous_patterns` (array): Map of patterns to error messages

**Adding custom patterns:**

```php
'dangerous_patterns' => [
    'rm -rf' => 'destructive deletion detected',
    'eval(' => 'eval usage detected',
    'import socket' => 'network access detected',
],
```

---

## Drivers

### LanguageDriver Interface

All language drivers must implement this interface.

```php
namespace MadeItEasyTools\Multiverse\Contracts;

interface LanguageDriver
{
    /**
     * Install dependencies for a worker.
     */
    public function installDependencies(string $workerPath): void;

    /**
     * Get the command to run a worker script.
     */
    public function getRunCommand(string $workerPath, string $scriptName): array;
}
```

---

### PythonDriver

Driver for executing Python workers.

#### `installDependencies(string $workerPath): void`

Installs Python packages from requirements.txt into the shared virtual environment.

**Parameters:**

- `$workerPath` (string): Path to worker directory (not used, uses shared venv)

**Throws:**

- `RuntimeException`: If venv not found or installation fails

---

#### `getRunCommand(string $workerPath, string $scriptName): array`

Builds the command to execute a Python script.

**Parameters:**

- `$workerPath` (string): Path to worker directory
- `$scriptName` (string): Script filename (usually 'main.py')

**Returns:**

- `array`: Command array for Symfony Process

**Security:**

- Scans file for dangerous patterns before execution
- Throws `RuntimeException` if dangerous code detected

**Example return:**

```php
[
    '/path/to/multiverse/python/venv/bin/python',
    '/path/to/multiverse/python/my_worker/main.py'
]
```

---

## Worker Contract

### Input

Workers receive JSON via stdin:

```python
import sys
import json

data = json.loads(sys.stdin.read())
# data is now a Python dict
```

### Output

Workers must output JSON to stdout:

```python
import json

result = {
    "status": "success",
    "message": "Processing complete",
    "data": {
        "output_path": "/tmp/result.jpg"
    }
}

print(json.dumps(result))
```

### Standard Response Format

**Success:**

```json
{
    "status": "success",
    "message": "Optional success message",
    "data": {
        "key": "value"
    }
}
```

**Error:**

```json
{
    "status": "error",
    "message": "Error description"
}
```

---

## Error Handling

### Common Exceptions

#### `InvalidArgumentException`

Thrown when:

- Worker name contains invalid characters
- Driver not configured

**Example:**

```php
try {
    MultiWorker::run('../../../etc/passwd', []);
} catch (InvalidArgumentException $e) {
    // "Invalid worker name"
}
```

---

#### `RuntimeException`

Thrown when:

- Worker not found
- Worker execution fails
- Security violation detected
- Python venv not found

**Example:**

```php
try {
    MultiWorker::run('nonexistent_worker', []);
} catch (RuntimeException $e) {
    // "Worker not found: nonexistent_worker"
}
```

---

## Events

Currently, the package does not fire Laravel events. This may be added in future versions.

**Potential future events:**

- `WorkerExecuting`
- `WorkerExecuted`
- `WorkerFailed`

---

## Testing

### Unit Testing Workers

```php
use MadeItEasyTools\Multiverse\Facades\MultiWorker;
use Tests\TestCase;

class ImageProcessorTest extends TestCase
{
    public function test_it_processes_images()
    {
        $result = MultiWorker::run('image_processor', [
            'image_url' => 'https://example.com/test.jpg',
            'filter' => 'blur'
        ]);

        $this->assertEquals('success', $result['status']);
        $this->assertArrayHasKey('output_path', $result['data']);
    }
}
```

### Mocking Workers

```php
use MadeItEasyTools\Multiverse\WorkerManager;
use Mockery;

public function test_with_mocked_worker()
{
    $mock = Mockery::mock(WorkerManager::class);
    $mock->shouldReceive('run')
        ->with('image_processor', Mockery::any())
        ->andReturn(['status' => 'success']);

    $this->app->instance(WorkerManager::class, $mock);

    // Your test code...
}
```

---

## Performance

### Benchmarks

Typical execution times:

| Operation                  | Time        |
| -------------------------- | ----------- |
| Simple echo worker         | ~20ms       |
| Image processing (Pillow)  | ~200-500ms  |
| Face detection (OpenCV)    | ~500-1500ms |
| ML inference (small model) | ~1-3s       |

### Optimization Tips

1. **Avoid repeated installs**: Dependencies are only installed once
2. **Use shared venv**: All workers share one environment
3. **Cache results**: Store frequently used outputs in Redis
4. **Queue heavy tasks**: Use Laravel queues for long-running workers
5. **Batch processing**: Process multiple items in one worker call

---

## Version History

### v1.0.0 (Current)

- Initial release
- Python support
- Security scanning
- Shared virtual environments
- Artisan commands

### Roadmap

- Node.js support
- Go support
- Worker events
- Performance monitoring
- Worker versioning
