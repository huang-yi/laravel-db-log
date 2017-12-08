<?php

namespace HuangYi\DBLog;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class Transformer
{
    /**
     * @var \Illuminate\Support\Collection
     */
    protected $events;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $content;

    /**
     * Transformer constructor.
     *
     * @param \Illuminate\Support\Collection $events
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(Collection $events, Request $request)
    {
        $this->events = $events;
        $this->request = $request;
    }

    /**
     * Transform.
     *
     * @return $this
     */
    public function transform()
    {
        $this->content = $this->formatEvents();

        if (! empty($this->content)) {
            $this->content = $this->formatRequest() . $this->content;
        }

        return $this;
    }

    /**
     * Format request.
     *
     * @return string
     */
    protected function formatRequest()
    {
        $method = $this->request->getMethod();
        $content = sprintf("%s %s", $method, $this->request->fullUrl());

        if ($method !== 'GET') {
            $content = sprintf("%s %s", $content, json_encode($this->request->all()));
        }

        return $content;
    }

    /**
     * Format events.
     *
     * @return string
     */
    protected function formatEvents()
    {
        $content = '';

        if ($this->events->isEmpty()) {
            return $content;
        }

        foreach ($this->events as $event) {
            $content .= $this->formatEvent($event);
        }

        return $content;
    }

    /**
     * Format event.
     *
     * @param mixed $event
     * @return string
     */
    protected function formatEvent($event)
    {
        $content = '';

        switch (true) {
            case $event instanceof QueryExecuted:
                $content = $this->formatQueryExecuted($event);
                break;

            case $event instanceof TransactionBeginning:
                $content = $this->formatTransactionBeginning($event);
                break;

            case $event instanceof TransactionCommitted:
                $content = $this->formatTransactionCommitted($event);
                break;

            case $event instanceof TransactionRolledBack:
                $content = $this->formatTransactionRolledBack($event);
                break;
        }

        return $content;
    }

    /**
     * Format query executed.
     *
     * @param \Illuminate\Database\Events\QueryExecuted $event
     * @return string
     */
    protected function formatQueryExecuted(QueryExecuted $event)
    {
        $sql = $event->sql;

        foreach ($event->bindings as $key => $value) {
            if (is_int($key)) {
                if (($start = strpos($sql, '?')) === false) {
                    continue;
                }

                $sql = substr_replace($sql, "'{$value}'", $start, 1);
            } else {
                $sql = str_replace(":{$key}", "'{$value}'", $sql);
            }
        }

        return sprintf("\n%s [%sms, %s]", $sql, $event->time, $event->connectionName);
    }

    /**
     * Format transaction beginning.
     *
     * @param \Illuminate\Database\Events\TransactionBeginning $event
     * @return string
     */
    protected function formatTransactionBeginning(TransactionBeginning $event)
    {
        return sprintf("\n[Transaction beginning: %s]", $event->connectionName);
    }

    /**
     * Format transaction committed.
     *
     * @param \Illuminate\Database\Events\TransactionCommitted $event
     * @return string
     */
    protected function formatTransactionCommitted(TransactionCommitted $event)
    {
        return sprintf("\n[Transaction committed: %s]", $event->connectionName);
    }

    /**
     * Format transaction rolledB back.
     *
     * @param \Illuminate\Database\Events\TransactionRolledBack $event
     * @return string
     */
    protected function formatTransactionRolledBack(TransactionRolledBack $event)
    {
        return sprintf("\n[Transaction rolled back: %s]", $event->connectionName);
    }

    /**
     * Return the content.
     *
     * @return string
     */
    public function __toString()
    {
        if (is_null($this->content)) {
            $this->transform();
        }

        return $this->content;
    }
}
