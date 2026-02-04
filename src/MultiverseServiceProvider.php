<?php

declare(strict_types=1);

namespace MadeItEasyTools\Multiverse;

use Illuminate\Support\ServiceProvider;
use MadeItEasyTools\Multiverse\Commands\MakeWorkerCommand;
use MadeItEasyTools\Multiverse\Commands\RunWorkerCommand;
use MadeItEasyTools\Multiverse\Commands\InstallLanguageCommand;
use MadeItEasyTools\Multiverse\Commands\UpdateLanguageCommand;
use MadeItEasyTools\Multiverse\Process\ProcessRunner;

class MultiverseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/multiverse.php', 'multiverse');

        $this->app->singleton(WorkerManager::class, function ($app) {
            return new WorkerManager($app['config'], $app[ProcessRunner::class]);
        });

        $this->app->bind(ProcessRunner::class, function ($app) {
            return new ProcessRunner;
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/multiverse.php' => config_path('multiverse.php'),
            ], 'multiverse-config');

            $this->commands([
                MakeWorkerCommand::class,
                RunWorkerCommand::class,
                InstallLanguageCommand::class,
                UpdateLanguageCommand::class,
            ]);
        }
    }
}
