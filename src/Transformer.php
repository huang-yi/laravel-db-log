<?php

namespace HuangYi\DBLog;

use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Request;

class Transformer
{
    /**
     * @var \Illuminate\Database\DatabaseManager
     */
    protected $db;

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
     * @param \Illuminate\Database\DatabaseManager $db
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(DatabaseManager $db, Request $request)
    {
        $this->db = $db;
        $this->request = $request;
    }

    /**
     * Transform.
     *
     * @return $this
     */
    public function transform()
    {
        $queries = $this->collectQueries();

        $this->content = $this->formatQueries($queries);

        return $this;
    }

    /**
     * Collect queries.
     *
     * @return array
     */
    protected function collectQueries()
    {
        $queries = [];

        foreach ($this->db->getConnections() as $name => $connection) {
            $logs = $connection->getQueryLog();

            if (empty($logs)) {
                continue;
            }

            $queries[$name] = $logs;
        }

        return $queries;
    }

    /**
     * Format queries.
     *
     * @param array $queries
     * @return string
     */
    protected function formatQueries(array $queries)
    {
        if (empty($queries)) {
            return '';
        }

        $content = $this->formatRequest();

        foreach ($queries as $connection => $logs) {
            $content .= $this->formatLogs($connection, $logs);
        }

        return $content;
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
     * Format logs.
     *
     * @param string $connection
     * @param array $logs
     * @return string
     */
    protected function formatLogs($connection, array $logs)
    {
        $content = "[{$connection}]\n";

        foreach ($logs as $query) {
            $content .= $this->formatSql($query);
        }

        return $content;
    }

    /**
     * Format sql.
     *
     * @param array $query
     * @return string
     */
    protected function formatSql(array $query)
    {
        $sql = $query['query'];

        foreach ($query['bindings'] as $key => $value) {
            if (is_int($key)) {
                if (($start = strpos($sql, '?')) !== false) {
                    continue;
                }

                $sql = substr_replace($sql, "'{$value}'", $start, 1);
            } else {
                $sql = str_replace(":{$key}", "'{$value}'", $sql);
            }
        }

        return sprintf("%s [%sms]\n", $sql, $query['time']);
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
