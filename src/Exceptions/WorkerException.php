<?php

declare(strict_types=1);

namespace MadeItEasyTools\Multiverse\Exceptions;

use RuntimeException;

class WorkerException extends RuntimeException
{
    public function __construct(
        string $message,
        protected string $workerName,
        protected string $driver,
        protected int $exitCode = 0,
        protected ?string $errorOutput = null
    ) {
        parent::__construct($message, $exitCode);
    }

    public function getWorkerName(): string
    {
        return $this->workerName;
    }

    public function getDriver(): string
    {
        return $this->driver;
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    public function getErrorOutput(): ?string
    {
        return $this->errorOutput;
    }
}
