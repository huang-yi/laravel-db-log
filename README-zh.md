# Laravel DB Log

该拓展包能帮助开发者记录所有的数据库查询。

## 安装

```shell
$ composer require huang-yi/laravel-db-log
```

## 使用

往项目的`.env`文件里填加一项配置即可：

```
DB_DEBUG=true
```

如果使用的Laravel版本小于5.5，则需要手动注册服务：

```php
<?php

// config/app.php

return [
    'providers' => [
        HuangYi\DBLog\ServiceProvider::class,
    ],
];
```

完成以上配置后，只要程序有数据库查询，都会往`storage/logs/sql.log`文件中打印所有执行过的SQL语句。

## 配置

> 一般情况下，开发者不需要修改任何配置即可正常使用该拓展包。

如果你需要做一些定制化的配置，可以将以下选项复制到`config/database.php`文件中（这一步是可选的）：

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

其中`debug`配置项为开关；

`log`为日志相关配置（与Laravel的日志配置一致）：
- `log.handler`可选值为`single`, `daily`, `syslog`, `errorlog`；
- `log.level`为Monolog的日志等级，可选值为`debug`, `info`, `notice`, `warning`, `error`, `critical`, `alter`, `emergency`；
- `log.channel`为Monolog的频道名；
- `log.max_files`只有`log.handler`值为`daily`时有效，表示日志文件最大保留数；

## License

The Laravel DB Log package is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
