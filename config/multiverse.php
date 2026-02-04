<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Workers Path
    |--------------------------------------------------------------------------
    |
    | The directory where your worker scripts are located.
    |
    */
    'workers_path' => base_path('multiverse'),

    /*
    |--------------------------------------------------------------------------
    | Language Drivers
    |--------------------------------------------------------------------------
    |
    | supported language drivers and their implementation classes.
    |
    */
    'drivers' => [
        'python' => \MadeItEasyTools\Multiverse\Drivers\PythonDriver::class,
        // 'node' => \MadeItEasyTools\Multiverse\Drivers\NodeDriver::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Python Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the python environment strategy.
    | 'venv_path': null (default) -> means isolated venv per worker.
    | 'venv_path': 'multiverse/python/venv' -> means shared venv.
    |
    */
    'python' => [
        // Path relative to base_path, or absolute
        'root_path' => 'multiverse/python',
        'venv_path' => 'multiverse/python/venv',
        'requirements_path' => 'multiverse/python/requirements.txt',
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Control the static analysis checks.
    |
    */
    'security' => [
        'scan_for_dangerous_code' => false,
        'dangerous_patterns' => [
            'rm -rf' => 'destructive deletion (rm -rf) detected',
            'shutil.rmtree' => 'directory deletion (shutil) detected',
            'mkfs' => 'disk formatting command detected',
            ':(){:|:&};:' => 'fork bomb detected',
            'dd if=/dev/zero' => 'disk wiping command detected',
            'IMPORT_DANGER_TEST' => 'test rule trigger', // Safe dummy rule for testing
        ],
    ],
];
