<?php

namespace HuangYi\DBLog\Tests;

use HuangYi\DBLog\Transformer;
use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class TransformerTest extends TestCase
{
    public function testTransform()
    {
        $transformer = new Transformer($this->makeEvents(), new Request);

        $content = (string) $transformer;

        $this->assertContains("[Transaction beginning: mysql]", $content);
        $this->assertContains("select * from `table1` where `foo` = 'foo' [0.1ms, mysql]", $content);
        $this->assertContains("select * from `table2` where `bar` = 'bar' [0.2ms, mysql]", $content);
        $this->assertContains("[Transaction committed: mysql]", $content);
    }

    protected function makeEvents()
    {
        $connection = $this->makeConnection();

        return Collection::make([
            new TransactionBeginning($connection),
            $this->makeQueryExecuted1(),
            $this->makeQueryExecuted2(),
            new TransactionCommitted($connection),
        ]);
    }

    protected function makeQueryExecuted1()
    {
        $sql = "select * from `table1` where `foo` = :foo";
        $bindings = [':foo' => 'foo'];
        $time = 0.1;
        $connection = $this->makeConnection();

        return new QueryExecuted($sql, $bindings, $time, $connection);
    }

    protected function makeQueryExecuted2()
    {
        $sql = "select * from `table2` where `bar` = ?";
        $bindings = ['bar'];
        $time = 0.2;
        $connection = $this->makeConnection();

        return new QueryExecuted($sql, $bindings, $time, $connection);
    }

    protected function makeConnection()
    {
        return new Connection('pdo', 'database', '', ['name' => 'mysql']);
    }
}
