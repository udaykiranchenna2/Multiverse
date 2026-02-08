<?php

declare(strict_types=1);

namespace MadeItEasyTools\Multiverse\Exceptions;

class TimeoutException extends WorkerException
{
    public function __construct(
        string $workerName,
        string $driver,
        float $timeout,
        ?string $errorOutput = null
    ) {
        parent::__construct(
            "Worker [{$workerName}] timed out after {$timeout} seconds.",
            $workerName,
            $driver,
            124, // Common exit code for timeout
            $errorOutput
        );
    }
}
