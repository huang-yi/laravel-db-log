<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Database Debug Mode
    |--------------------------------------------------------------------------
    |
    | When database is in debug mode, all database queries will be logged.
    |
    */
    'debug' => env('DB_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    */
    'log' => [
        'handler' => env('DB_LOG', 'single'),

        'level' => env('DB_LOG_LEVEL', 'debug'),

        'channel' => env('DB_LOG_CHANNEL', 'sql'),

        'max_files' => env('DB_LOG_MAX_FILES', 5),
    ],
];
