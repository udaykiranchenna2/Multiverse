<?php

declare(strict_types=1);

namespace MadeItEasyTools\Multiverse\Contracts;

interface LanguageDriver
{
    /**
     * Install dependencies for the worker.
     */
    public function installDependencies(string $workerPath): void;

    /**
     * Get the command to run the worker file.
     *
     * @return array<int, string>
     */
    public function getRunCommand(string $workerPath, string $scriptName): array;
}
