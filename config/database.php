<?php

return [
    'debug' => env('DB_DEBUG', false),

    'log' => [
        'handler' => env('DB_LOG', 'single'),
        'level' => env('DB_LOG_LEVEL', 'debug'),
        'channel' => env('DB_LOG_CHANNEL', 'sql'),
        'max_files' => env('DB_LOG_MAX_FILES', 5),
    ],
];
