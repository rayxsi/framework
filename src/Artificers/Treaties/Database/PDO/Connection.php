<?php

namespace Artificers\Treaties\Database\PDO;

use Artificers\Treaties\Database\Driver\Exception;

interface Connection {

    /**
     * Prepares a statement for execution and returns a driver statement object.
     *
     * @param string $sql
     * @return Statement
     *
     * @throws Exception
     */
    public function prepare(string $sql): Statement;

    /**
     * Execute an SQL statement and return the Result.
     *
     * @param string $sql
     * @return int
     *
     * @throws Exception
     */
    public function exec(string $sql): int;

    /**
     * prepares and executes an SQL statement in a single function call, returning the driver Result.
     *
     * @param string $sql
     * @return Result
     *
     * @throws Exception
     */
    public function query(string $sql): Result;

    /**
     * Quotes a string for use in a query.
     *
     * @param string $string
     * @param $type
     * @return string|false
     */
    public function quote(string $string, $type): string|false;

    /**
     * Start transaction. Turns off autocommit mode.
     *
     * @return bool
     * @throws Exception
     */
    public function startTransaction():bool;

    /**
     * Commits a transaction.
     *
     * @return bool
     * @throws Exception
     */
    public function commit(): bool;

    /**
     * Rolls back a transaction.
     *
     * @return bool
     * @throws Exception
     */
    public function rollback(): bool;

    /**
     * Returns the ID of the last inserted row or sequence value.
     *
     * @param string|null $name
     * @return string|false
     * @throws Exception
     */
    public function lastInsertedId(?string $name=null): string|false;

    /**
     * Checks if inside a transaction.
     *
     * @return bool
     */
    public  function inTransaction(): bool;
}