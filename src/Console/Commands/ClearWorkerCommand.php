<?php

declare(strict_types=1);

namespace MadeItEasyTools\Multiverse\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class ClearWorkerCommand extends Command
{
    protected $signature = 'multiverse:clear {worker? : The worker name to clear processes for}';

    protected $description = 'Kill all running processes for a specific worker or all multiverse workers';

    public function handle(): int
    {
        $workerName = $this->argument('worker');

        if ($workerName) {
            $this->info("Searching for processes matching worker: {$workerName}");
            $killed = $this->killWorkerProcesses($workerName);
        } else {
            $this->info('Searching for all multiverse worker processes...');
            $killed = $this->killAllMultiverseProcesses();
        }

        if ($killed > 0) {
            $this->info("✓ Killed {$killed} process(es)");

            return self::SUCCESS;
        }

        $this->warn('No matching processes found');

        return self::SUCCESS;
    }

    protected function killWorkerProcesses(string $workerName): int
    {
        // Find processes containing the worker path
        $pythonRoot = base_path(config('multiverse.python.root_path', 'multiverse/python'));
        $searchPattern = "{$pythonRoot}/{$workerName}";

        return $this->findAndKillProcesses($searchPattern);
    }

    protected function killAllMultiverseProcesses(): int
    {
        $pythonRoot = base_path(config('multiverse.python.root_path', 'multiverse/python'));

        return $this->findAndKillProcesses($pythonRoot);
    }

    protected function findAndKillProcesses(string $pattern): int
    {
        // Use pgrep to find processes matching the pattern
        $process = new Process(['pgrep', '-f', $pattern]);
        $process->run();

        if (! $process->isSuccessful()) {
            return 0;
        }

        $pids = array_filter(explode("\n", trim($process->getOutput())));

        if (empty($pids)) {
            return 0;
        }

        // Kill each process
        $killed = 0;
        foreach ($pids as $pid) {
            $killProcess = new Process(['kill', '-9', $pid]);
            $killProcess->run();

            if ($killProcess->isSuccessful()) {
                $killed++;
                $this->line("  Killed PID: {$pid}");
            }
        }

        return $killed;
    }
}
