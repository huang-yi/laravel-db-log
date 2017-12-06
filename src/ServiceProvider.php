<?php

namespace HuangYi\DBLog;

use Illuminate\Log\Writer;
use Illuminate\Support\ServiceProvider as BaseServiceProvidor;
use Monolog\Logger as Monolog;

class ServiceProvider extends BaseServiceProvidor
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/database.php', 'database');

        $this->app->singleton('db.log', function () {
            return $this->createLogger();
        });

        $this->logQueries();
    }

    /**
     * Log queries.
     */
    protected function logQueries()
    {
        if (! $this->debugging()) {
            return;
        }

        $this->enableQueryLog();

        register_shutdown_function([$this, 'onShutdown']);
    }

    /**
     * @return bool
     */
    protected function debugging()
    {
        return $this->app['config']['database.debug'];
    }

    /**
     * Enable query log.
     */
    protected function enableQueryLog()
    {
        foreach ($this->app['db']->getConnections() as $connection) {
            $connection->enableQueryLog();
        }
    }

    /**
     * "onShutdown" Handler.
     */
    public function onShutdown()
    {
        $transformer = new Transformer($this->app['db'], $this->app['request']);
        $content = (string) $transformer;

        if (empty($content)) {
            return;
        }

        $level = $this->app['config']['database.log.level'];

        $this->app['db.log']->log($level, $content);
    }

    /**
     * Create the logger.
     *
     * @return \Illuminate\Log\Writer
     */
    public function createLogger()
    {
        $log = new Writer(
            new Monolog($this->channel()), $this->app['events']
        );

        if ($this->app->hasMonologConfigurator()) {
            call_user_func($this->app->getMonologConfigurator(), $log->getMonolog());
        } else {
            $this->configureHandler($log);
        }

        return $log;
    }

    /**
     * Get the name of the log "channel".
     *
     * @return string
     */
    protected function channel()
    {
        if ($this->app->bound('config') &&
            $channel = $this->app->make('config')->get('database.log.channel')) {
            return $channel;
        }

        return $this->app->bound('env') ? $this->app->environment() : 'production';
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Log\Writer  $log
     * @return void
     */
    protected function configureHandler(Writer $log)
    {
        $this->{'configure'.ucfirst($this->handler()).'Handler'}($log);
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Log\Writer  $log
     * @return void
     */
    protected function configureSingleHandler(Writer $log)
    {
        $log->useFiles(
            $this->app->storagePath().'/logs/sql.log',
            $this->logLevel()
        );
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Log\Writer  $log
     * @return void
     */
    protected function configureDailyHandler(Writer $log)
    {
        $log->useDailyFiles(
            $this->app->storagePath().'/logs/sql.log', $this->maxFiles(),
            $this->logLevel()
        );
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Log\Writer  $log
     * @return void
     */
    protected function configureSyslogHandler(Writer $log)
    {
        $log->useSyslog('sql', $this->logLevel());
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Log\Writer  $log
     * @return void
     */
    protected function configureErrorlogHandler(Writer $log)
    {
        $log->useErrorLog($this->logLevel());
    }

    /**
     * Get the default log handler.
     *
     * @return string
     */
    protected function handler()
    {
        if ($this->app->bound('config')) {
            return $this->app->make('config')->get('database.log.handler', 'single');
        }

        return 'single';
    }

    /**
     * Get the log level for the application.
     *
     * @return string
     */
    protected function logLevel()
    {
        if ($this->app->bound('config')) {
            return $this->app->make('config')->get('database.log.level', 'debug');
        }

        return 'debug';
    }

    /**
     * Get the maximum number of log files for the application.
     *
     * @return int
     */
    protected function maxFiles()
    {
        if ($this->app->bound('config')) {
            return $this->app->make('config')->get('database.log.max_files', 5);
        }

        return 0;
    }
}
