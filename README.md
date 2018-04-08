# Laravel DB Log

[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://api.travis-ci.org/huang-yi/laravel-db-log.svg?branch=master)](https://travis-ci.org/huang-yi/laravel-db-log)

This package logs your database queries.

## Version Compatibility

Laravel | Laravel-DB-Log
:--:|:--:
<=5.5 | [1.5](https://github.com/huang-yi/laravel-db-log/blob/1.5/README.md)
5.6 | 1.6

## Installation

```shell
$ composer require huang-yi/laravel-db-log:1.6.*
```

## Usage

Add this configuration to your application's `.env` file:

```
DB_LOG=true
```

## Configuration

> In general, developers do not need to modify any configurations.

If you don't want to keep the default configurations, just copy the following options into the `config/logging.php` file:

```php
<?php

return [
    'channels' => [
        'db' => [
            'debug' => env('DB_LOG', false),
            'name' => env('DB_LOG_NAME', 'sql'),
            'driver' => env('DB_LOG_DRIVER', 'daily'),
            'path' => storage_path('logs/sql.log'),
            'level' => env('DB_LOG_LEVEL', 'debug'),
            'days' => env('DB_LOG_MAX_FILES', 2),
        ],
    ],
];
```

## Chinese Doc

[中文文档](README-zh.md)

## License

The Laravel DB Log package is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
