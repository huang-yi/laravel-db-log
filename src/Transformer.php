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
                if (($start = strpos($sql, '?')) !== false) {
                    continue;
                }

                $sql = substr_replace($sql, "'{$value}'", $start, 1);
            } else {
                $sql = str_replace(":{$key}", "'{$value}'", $sql);
            }
        }

        return sprintf("%s [%sms, %s]\n", $sql, $event->time, $event->connectionName);
    }

    /**
     * Format transaction beginning.
     *
     * @param \Illuminate\Database\Events\TransactionBeginning $event
     * @return string
     */
    protected function formatTransactionBeginning(TransactionBeginning $event)
    {
        return sprintf("[Transaction beginning: %s]\n", $event->connectionName);
    }

    /**
     * Format transaction committed.
     *
     * @param \Illuminate\Database\Events\TransactionCommitted $event
     * @return string
     */
    protected function formatTransactionCommitted(TransactionCommitted $event)
    {
        return sprintf("[Transaction committed: %s]\n", $event->connectionName);
    }

    /**
     * Format transaction rolledB back.
     *
     * @param \Illuminate\Database\Events\TransactionRolledBack $event
     * @return string
     */
    protected function formatTransactionRolledBack(TransactionRolledBack $event)
    {
        return sprintf("[Transaction rolled back: %s]\n", $event->connectionName);
    }

    /**
     * Format request.
     *
     * @return string
     */
    protected function formatRequest()
    {
        $inputs = $this->request->input();
        $content = sprintf("Request: %s.\n", $this->request->fullUrl());

        if (! empty($inputs)) {
            $content .= sprintf("Inputs: %s.\n", json_encode($inputs));
        }

        return $content;
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
