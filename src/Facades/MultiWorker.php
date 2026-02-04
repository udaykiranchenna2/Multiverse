<?php

declare(strict_types=1);

namespace MadeItEasyTools\Multiverse\Facades;

use Illuminate\Support\Facades\Facade;
use MadeItEasyTools\Multiverse\WorkerManager;
/**
 * @method static array run(string $workerName, array $payload = [])
 * @method static \MadeItEasyTools\MultiLanguage\Contracts\LanguageDriver driver(string $driver)
 *
 * @see \MadeItEasyTools\MultiLanguage\WorkerManager
 */
class MultiWorker extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return WorkerManager::class;
    }
}
