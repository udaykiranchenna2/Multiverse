<?php

declare(strict_types=1);

namespace MadeItEasyTools\Multiverse\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use MadeItEasyTools\Multiverse\Process\ProcessRunner;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\spin;

class UpdateLanguageCommand extends Command
{
    protected $signature = 'multiverse:update {--lang= : Language to update (python, node)}';

    protected $description = 'Update dependencies for a language runtime environment';

    public function __construct(
        protected ProcessRunner $processRunner
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $lang = $this->option('lang');

        if (! $lang) {
            error('Please specify a language using --lang option');
            info('Example: php artisan multiverse:update --lang=python');

            return;
        }

        match ($lang) {
            'python' => $this->updatePython(),
            'node' => $this->updateNode(),
            default => error("Language [{$lang}] is not supported yet. Available: python, node")
        };
    }

    protected function updatePython(): void
    {
        info('🐍 Updating Python dependencies...');

        $venvPath = base_path(config('multiverse.python.venv_path', 'multiverse/python/venv'));
        $reqPath = base_path(config('multiverse.python.requirements_path', 'multiverse/python/requirements.txt'));
        $pipPath = $venvPath.'/bin/pip';

        // Check if environment exists
        if (! is_dir($venvPath)) {
            error("Virtual environment not found at: {$venvPath}");
            error("Please run 'php artisan multiverse:install --lang=python' first.");

            return;
        }

        if (! File::exists($reqPath)) {
            error("requirements.txt not found at: {$reqPath}");

            return;
        }

        // Install Dependencies
        info('Installing/Updating dependencies from requirements.txt...');

        try {
            spin(
                fn () => $this->processRunner->run([$pipPath, 'install', '-r', $reqPath]),
                'Processing requirements...'
            );

            info('✓ Dependencies updated successfully');
        } catch (\Exception $e) {
            error('Failed to update dependencies.');
            error($e->getMessage());
        }
    }

    protected function updateNode(): void
    {
        error('Node.js support is coming soon!');
    }
}
