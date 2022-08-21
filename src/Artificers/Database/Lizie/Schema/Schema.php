<?php

namespace Artificers\Database\Lizie\Schema;

use Artificers\Database\Lizie\Connections\Connection;
use Artificers\Database\Lizie\Driver\Exception;
use Artificers\Database\Lizie\Schema\Grammars\Grammar;
use Closure;
use LogicException;

class Schema {
    protected Connection $connection;

    protected Grammar $grammar;

    public function __construct(Connection $connection) {
        $this->connection = $connection;
        $this->grammar = $connection->getSchemaGrammar();
    }

    /**
     * Create table in database.
     * @param string $tableName
     * @param Closure|null $closure
     * @throws Exception
     */
    public function make(string $tableName, Closure|null $closure): void {
      $this->build(auto($this->createBluePrint($tableName), function($table) use($closure) {
            $table->create();
            $closure($table);
        }));
    }

    /**
     * Create table instance.
     * @param string $tableName
     * @return Table
     */
    private function createBluePrint(string $tableName): Table {
        return new Table($tableName);
    }

    /**
     * Build table.
     * @param Table $table
     * @throws Exception
     */
    private function build(Table $table): void {
        $table->build($this->connection, $this->grammar);
    }

    /**
     * Give functionality to modify in a table.
     * @param string $tableName
     * @param Closure $callback
     * @throws Exception
     */
    public function modify(string $tableName, Closure $callback): void {
        $this->build(auto($this->createBluePrint($tableName), function($table) use($callback) {
            $callback($table);
        }));
    }

    /**
     * Check if table exists in this schema.
     * @param string $table
     * @return bool
     * @throws Exception
     * @throws \Artificers\Treaties\Database\Driver\Exception
     */
    public function exists(string $table): bool {
        $result = $this->connection->runQuery($this->grammar->compileTableExists(), [$this->connection->getSchema(), $table]);
        return count($result->fetchAllRowsAsNumeric()) > 0;
    }

    /**
     * Create database schema
     * @param string $name
     * @return bool
     * @throws Exception
     */
    public function createDatabase(string $name): bool {
        if($this->connection->runQuery($this->grammar->compileCreateDatabaseIfNotExists($name))) {
            return true;
        }

        return false;
    }

    /**
     * Help to use different database.
     * @param string $database
     * @return bool
     * @throws Exception
     */
    public function use(string $database): bool {
        if($this->connection->runQuery($this->grammar->compileUseDatabase($database))) {
            return true;
        }

        return false;
    }

    /**
     * Drop database schema.
     *
     * @return bool
     * @throws Exception
     */
    public function dropIfExists(): bool {
        if($this->connection->runQuery($this->grammar->compileDropDatabaseIfExists($this->connection->getSchema()))) {
            return true;
        }

        return false;
    }

    /**
     * Drop table if exists.
     *
     * @param string $tableName
     * @throws Exception
     */
    public function dropTableIfExists(string $tableName): void {
        $this->build(auto($this->createBluePrint($tableName), function($table) {
            $table->dropIfExists();
        }));
    }

    /**
     * Rename existing table.
     *
     * @param string $old
     * @param string $new
     * @return bool
     * @throws Exception
     */
    public function rename(string $old, string $new): bool {
        if($this->connection->runQuery($this->grammar->compileTableRename($old, $new))) return true;

        return false;
    }

    /**
     * Enable foreign key constraints.
     *
     * @return bool
     * @throws Exception
     */
    public function enableForeignKeyConstraints(): bool {
        if($this->connection->runQuery($this->grammar->compileEnableForeignKeyConstraints())) return  true;
        return false;
    }

    /**
     * Disable foreign key constraints.
     *
     * @return bool
     * @throws Exception
     */
    public function disableForeignKeyConstraints(): bool {
        if($this->connection->runQuery($this->grammar->compileDisableForeignKeyConstraints())) return  true;
        return false;
    }

    /**
     * Return all the tables.
     *
     * @return array
     */
    public function getAllTable(): array {
        try {
            $this->connection->runQuery($this->grammar->compileGetAllTables());
        }catch(Exception $e) {
            throw new LogicException("This database driver does not support getting all tables.");
        }
    }
}