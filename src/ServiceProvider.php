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
        $this->mergeConfigFrom(__DIR__ . '/../config/logging.php', 'logging.channels');

        if (! $this->debugging()) {
            return;
        }

        $this->app->singleton('db.log', function () {
            return $this->app['db']->channel('db');
        });

        $this->app->singleton('db.events', function () {
            return Collection::make();
        });

        $this->listenEvents();

        register_shutdown_function([$this, 'logQueries']);
    }

    /**
     * @return bool
     */
    protected function debugging()
    {
        return $this->app['config']['logging.channels.db.debug'];
    }

    /**
     * Listen events.
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
