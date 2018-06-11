<?php

namespace HuangYi\DBLog;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/channels.php', 'logging.channels');

        if (! $this->debugging()) {
            return;
        }

        $this->registerDbLog();

        $this->registerDbEvents();

        $this->listenEvents();

        $this->logWhenTerminating();
    }

    /**
     * Register db log.
     *
     * @return void
     */
    protected function registerDbLog()
    {
        $this->app->singleton('db.log', function () {
            return $this->app['log']->channel('db');
        });
    }

    /**
     * Register db events.
     *
     * @return void
     */
    protected function registerDbEvents()
    {
        $this->app->singleton('db.events', function () {
            return Collection::make();
        });
    }

    /**
     * Log when terminating application.
     *
     * @return void
     */
    protected function logWhenTerminating()
    {
        $this->app->terminating(function () {
            $this->logQueries();

            $this->registerDbEvents();
        });
    }

    /**
     * Debugging status.
     *
     * @return bool
     */
    protected function debugging()
    {
        return $this->app['config']['logging.channels.db.debug'];
    }

    /**
     * Listen events.
     *
     * @return void
     */
    protected function listenEvents()
    {
        $events = [
            QueryExecuted::class,
            TransactionBeginning::class,
            TransactionCommitted::class,
            TransactionRolledBack::class,
        ];

        foreach ($events as $event) {
            $this->app['events']->listen($event, function ($event) {
                $this->app['db.events']->push($event);
            });
        }
    }

    /**
     * Log queries.
     *
     * @return void
     */
    public function logQueries()
    {
        $transformer = new Transformer($this->app['db.events'], $this->app['request']);
        $content = (string) $transformer;

        if (empty($content)) {
            return;
        }

        $level = $this->app['config']['logging.channels.db.level'];

        $this->app['db.log']->log($level, $content);
    }
}
