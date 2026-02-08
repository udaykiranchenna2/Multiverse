# Python Support Improvements - Implementation Summary

## ✅ Completed Features

### 1. Exception Handling

- **WorkerException**: Base exception class for all worker-related errors
  - Stores worker name, driver, exit code, and stderr output
  - Provides getter methods for debugging
- **TimeoutException**: Specific exception for timeout scenarios
  - Extends WorkerException
  - Automatically includes timeout duration in message

### 2. Flexible Timeout Configuration

- **Default**: Unlimited (`null`) - workers run until completion
- **Global Config**: Set in `config/multiverse.php`
- **Per-Call Override**: Pass `_timeout` in payload
  ```php
  MultiWorker::run('video_render', ['_timeout' => 300]); // 5 minutes
  ```

### 3. Automated Error Logging

- **Configurable**: Enable/disable via `multiverse.logging.enabled`
- **Channel Selection**: Use any Laravel log channel
- **Rich Context**: Logs include:
  - Worker name
  - Full payload (for reproduction)
  - Error message
  - Exception class

### 4. Manual Process Cleanup

- **Command**: `php artisan multiverse:clear {worker?}`
- **Usage**:

  ```bash
  # Clear specific worker
  php artisan multiverse:clear video_processor

  # Clear all multiverse workers
  php artisan multiverse:clear
  ```

- **How it works**: Uses `pgrep` to find processes, then `kill -9` to terminate

### 5. Improved ProcessRunner

- **Timeout Support**: Accepts optional timeout parameter
- **Better Error Messages**: Includes both stderr and stdout
- **Exit Code Preservation**: Maintains process exit codes for debugging

## Configuration

Add to your `config/multiverse.php`:

```php
// Timeout (null = unlimited)
'timeout' => null,

// Logging
'logging' => [
    'enabled' => true,
    'channel' => env('LOG_CHANNEL', 'stack'),
],
```

## Usage Examples

### Basic Worker Execution

```php
use MadeItEasyTools\Multiverse\Facades\MultiWorker;
use MadeItEasyTools\Multiverse\Exceptions\WorkerException;
try {
    $result = MultiWorker::run('image_processor', [
        'image_url' => 'https://example.com/image.jpg'
    ]);
} catch (WorkerException $e) {
    // Worker failed - check logs for details
    Log::error('Image processing failed', [
        'worker' => $e->getWorkerName(),
        'stderr' => $e->getErrorOutput(),
    ]);
}
```

### With Custom Timeout

```php
// Long-running task with 10-minute timeout
$result = MultiWorker::run('video_render', [
    'video_path' => '/path/to/video.mp4',
    '_timeout' => 600, // 10 minutes
]);
```

### Cleanup Zombie Processes

```bash
# If a worker hangs, manually kill it
php artisan multiverse:clear video_render
```

## Files Modified/Created

### New Files

- `src/Exceptions/WorkerException.php`
- `src/Exceptions/TimeoutException.php`
- `src/Commands/ClearWorkerCommand.php`

### Modified Files

- `src/Process/ProcessRunner.php` - Added timeout support
- `src/WorkerManager.php` - Added error handling and logging
- `src/MultiverseServiceProvider.php` - Registered new command
- `config/multiverse.php` - Added timeout and logging config

## Next Steps

1. **Publish Updated Config**: Run `php artisan vendor:publish --tag=multiverse-config --force`
2. **Test Error Scenarios**: Create a worker that intentionally fails to verify logging
3. **Test Timeout**: Create a worker with `time.sleep(10)` and set `_timeout => 2`
4. **Test Cleanup**: Run a long worker and use `multiverse:clear` to kill it
