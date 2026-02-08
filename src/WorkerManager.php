<?php

declare(strict_types=1);

namespace MadeItEasyTools\Multiverse;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use InvalidArgumentException;
use MadeItEasyTools\Multiverse\Contracts\LanguageDriver;
use MadeItEasyTools\Multiverse\Process\ProcessRunner;
use RuntimeException;

/**
 * WorkerManager
 *
 * Core class responsible for executing workers written in other languages.
 * Handles driver resolution, path management, security checks, and process execution.
 */
class WorkerManager
{
    /** @var array<string, LanguageDriver> Cached language driver instances */
    protected array $drivers = [];

    public function __construct(
        protected ConfigRepository $config,
        protected ProcessRunner $processRunner
    ) {}

    /**
     * Execute a worker with the given payload.
     *
     * This method:
     * 1. Validates the worker name for security
     * 2. Resolves the appropriate language driver
     * 3. Locates the worker script
     * 4. Executes the worker with the provided payload
     * 5. Returns the parsed JSON output
     *
     * @param  string  $workerName  The name of the worker to execute (alphanumeric, dashes, underscores only)
     * @param  array<string, mixed>  $payload  Data to pass to the worker as JSON
     * @return array<string, mixed> The worker's output parsed as an associative array
     *
     * @throws InvalidArgumentException If worker name contains invalid characters
     * @throws RuntimeException If worker is not found or execution fails
     *
     * @example
     * ```php
     * $result = $workerManager->run('image_processor', [
     *     'image_url' => 'https://example.com/image.jpg',
     *     'filter' => 'blur'
     * ]);
     * ```
     */
    public function run(string $workerName, array $payload = []): array
    {
        // SECURITY: Prevent Path Traversal
        if (! preg_match('/^[a-zA-Z0-9_-]+$/', $workerName)) {
            throw new InvalidArgumentException("Invalid worker name [{$workerName}]. Only alphanumeric characters, dashes, and underscores are allowed.");
        }

        // 1. Resolve Driver (currently defaulting to python or configurable)
        // For MVP, lets assume we check the config or file extension.
        // We'll require 'driver' in payload or config for now, or just default to python.
        $driverName = $payload['driver'] ?? 'python';
        $driver = $this->driver($driverName);

        // 2. Determine paths
        // 2. Determine paths
        $workersPath = $this->config->get('multiverse.workers_path');

        // Try language-specific subfolder first: workers/python/worker_name
        $driverSpecificPath = rtrim($workersPath, '/').'/'.$driverName.'/'.$workerName;

        // Fallback to flat structure: workers/worker_name
        $flatPath = rtrim($workersPath, '/').'/'.$workerName;

        $scriptName = 'main.py'; // Default

        if (is_dir($driverSpecificPath)) {
            $workerPath = $driverSpecificPath;
        } elseif (is_dir($flatPath)) {
            $workerPath = $flatPath;
        } else {
            // Fallback: check if it's just a file
            if (file_exists($driverSpecificPath.'.py')) {
                $workerPath = dirname($driverSpecificPath);
                $scriptName = basename($driverSpecificPath).'.py';
            } elseif (file_exists($flatPath.'.py')) {
                $workerPath = dirname($flatPath);
                $scriptName = basename($flatPath).'.py';
            } else {
                throw new RuntimeException("Worker not found: {$workerName} (checked {$driverSpecificPath}, {$flatPath})");
            }
        }

        // Performance Optimization:
        // We do NOT call $driver->installDependencies() here anymore.
        // Dependency installation should be handled by the deployment pipeline
        // or explicitly via 'php artisan multiverse:install' or a dedicated update command.
        // This avoids a 300ms+ overhead on every worker execution.
        // if ($this->config->get('multiverse.auto_sync_dependencies', false)) {
        //    $driver->installDependencies($workerPath);
        // }

        // 3. Build Command
        $command = $driver->getRunCommand($workerPath, $scriptName);

        // 4. Encode Payload
        $input = json_encode($payload);

        // 5. Get Timeout (from payload override or config default)
        $timeout = $payload['_timeout'] ?? $this->config->get('multiverse.timeout', null);

        // 6. Execute with error handling and logging
        try {
            $output = $this->processRunner->run($command, $workerPath, null, $input, $timeout);
        } catch (\Symfony\Component\Process\Exception\ProcessTimedOutException $e) {
            $exception = new \MadeItEasyTools\Multiverse\Exceptions\TimeoutException(
                $workerName,
                $driverName,
                $timeout
            );

            $this->logWorkerError($workerName, $payload, $exception);

            throw $exception;
        } catch (RuntimeException $e) {
            $exception = new \MadeItEasyTools\Multiverse\Exceptions\WorkerException(
                "Worker [{$workerName}] failed: ".$e->getMessage(),
                $workerName,
                $driverName,
                $e->getCode(),
                $e->getMessage()
            );

            $this->logWorkerError($workerName, $payload, $exception);

            throw $exception;
        }

        // 7. Parse Output
        $result = json_decode($output, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // If output is not JSON, return raw
            return [
                'status' => 'success', // or unknown
                'raw_output' => $output,
            ];
        }

        return $result;
    }

    /**
     * Log worker errors to Laravel log if logging is enabled.
     */
    protected function logWorkerError(string $workerName, array $payload, \Exception $exception): void
    {
        if (! $this->config->get('multiverse.logging.enabled', true)) {
            return;
        }

        \Illuminate\Support\Facades\Log::channel(
            $this->config->get('multiverse.logging.channel', 'stack')
        )->error("Multiverse Worker Failed: {$workerName}", [
            'worker' => $workerName,
            'payload' => $payload,
            'error' => $exception->getMessage(),
            'exception' => get_class($exception),
        ]);
    }

    public function driver(string $driver): LanguageDriver
    {
        if (isset($this->drivers[$driver])) {
            return $this->drivers[$driver];
        }

        $driverClass = $this->config->get("multiverse.drivers.{$driver}");

        if (! $driverClass || ! class_exists($driverClass)) {
            throw new InvalidArgumentException("Driver [{$driver}] not configured or class not found.");
        }

        $this->drivers[$driver] = app($driverClass);

        return $this->drivers[$driver];
    }

    // Queue method removed as per user request to let developer handle job dispatching manually if needed.
}
