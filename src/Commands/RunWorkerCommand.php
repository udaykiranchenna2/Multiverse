<?php

declare(strict_types=1);

namespace MadeItEasyTools\Multiverse\Commands;

use Illuminate\Console\Command;
use MadeItEasyTools\Multiverse\Facades\MultiWorker;

class RunWorkerCommand extends Command
{
    protected $signature = 'worker:run 
                            {worker : The name of the worker to run} 
                            {--payload= : JSON payload to send to the worker}';

    protected $description = 'Run a multi-language worker manually';

    public function handle(): void
    {
        $workerName = $this->argument('worker');
        $payloadStr = $this->option('payload');

        // Handle case where payload option is null or empty string despite default
        if (empty($payloadStr)) {
            $payloadStr = '{}';
        }

        $payload = json_decode($payloadStr, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // Fallback: try to see if it's a simple string that didn't get quoted?
            // Or just error out more descriptively
            $this->error('Invalid JSON payload: '.json_last_error_msg());
            $this->error('Payload was: '.$payloadStr);

            return;
        }

        $this->info("Running worker [{$workerName}]...");

        try {
            $startTime = microtime(true);
            $result = MultiWorker::run($workerName, $payload);
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            $this->info("Worker finished in {$duration}ms");

            $this->info('Output:');
            $this->line(json_encode($result, JSON_PRETTY_PRINT));

        } catch (\Exception $e) {
            $this->error('Execution Failed: '.$e->getMessage());
        }
    }
}
