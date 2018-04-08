# Laravel DB Log

该拓展包能帮助开发者记录所有的数据库查询。

## 版本兼容

Laravel | Laravel-DB-Log
:--:|:--:
<=5.5 | [1.5](https://github.com/huang-yi/laravel-db-log/blob/1.5/README-zh.md)
5.6 | 1.6

## 安装

```shell
$ composer require huang-yi/laravel-db-log:1.6.*
```

## 使用

往项目的`.env`文件里填加一项配置即可：

```
DB_LOG=true
```

## 配置

> 一般情况下，开发者不需要修改任何配置即可正常使用该拓展包。

如果你需要做一些定制化的配置，可以在系统配置文件`config/logging.php`中填加一项名为`db`的`channel`（这一步是可选的）：

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

## License

The Laravel DB Log package is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
