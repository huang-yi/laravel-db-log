<?php

return [
    'db' => [
        'debug' => env('DB_LOG', false),
        'name' => env('DB_LOG_NAME', 'sql'),
        'driver' => env('DB_LOG_DRIVER', 'daily'),
        'path' => storage_path('logs/sql.log'),
        'level' => env('DB_LOG_LEVEL', 'debug'),
        'days' => env('DB_LOG_MAX_FILES', 2),
    ],
];
