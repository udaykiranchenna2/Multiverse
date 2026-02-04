<?php

declare(strict_types=1);

namespace MadeItEasyTools\Multiverse\Process;

use RuntimeException;
use Symfony\Component\Process\Process;

class ProcessRunner
{
    public function run(array $command, ?string $cwd = null, ?array $env = null, ?string $input = null): string
    {
        // Set timeout to null (unlimited) or a configurable value
        $process = new Process($command, $cwd, $env, $input, null);

        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException($process->getErrorOutput());
        }

        return $process->getOutput();
    }
}
