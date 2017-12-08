# Laravel DB Log

[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://api.travis-ci.org/huang-yi/laravel-db-log.svg?branch=master)](https://travis-ci.org/huang-yi/laravel-db-log)

This package logs your database queries.

## Installation

```shell
$ composer require huang-yi/laravel-db-log
```

## Usage

Add this configuration to your application's `.env` file:

```
DB_DEBUG=true
```

And, you should manually register the service provider if the Laravel version is less than 5.5:

```php
<?php

// config/app.php

return [
    'providers' => [
        HuangYi\DBLog\ServiceProvider::class,
    ],
];
```

Then, all the database queries will be logged into the `storage/logs/sql.log` file by default.

## Configuration

> In general, developers do not need to modify any configurations.

If you don't want to keep the default configurations, just copy the following options into the `config/database.php` file:

```php
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
```

The `log` option is similar to Laravel's log.

## Chinese Doc

[中文文档](README-zh.md)

## License

The Laravel DB Log package is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
