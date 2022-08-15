<?php

namespace Artificers\Database\Lizie\Schema;

use Artificers\Database\Lizie\Connections\Connection;
use Artificers\Database\Lizie\Driver\Exception;
use Artificers\Database\Lizie\Schema\Grammars\Grammar;
use Closure;

class Schema {
    protected Connection $connection;

    protected Grammar $grammar;

    public function __construct(Connection $connection) {
        $this->connection = $connection;
        $this->grammar = $connection->getSchemaGrammar();
    }

    /**
     * @throws Exception
     */
    public function make(string $tableName, Closure|null $closure): void {
      $this->build(auto($this->createBluePrint($tableName), function($table) use($closure) {
            $table->create();
            $closure($table);
        }));
    }

    private function createBluePrint(string $tableName): Table {
        return new Table($tableName);
    }

    /**
     * @throws Exception
     */
    private function build(Table $table): void {
        $table->build($this->connection, $this->grammar);
    }

    /**
     * @throws Exception
     */
    public function dropTableIfExists(string $tableName): void {
        $this->build(auto($this->createBluePrint($tableName), function($table) {
            $table->dropIfExists();
        }));
    }

    /**
     * @throws Exception
     */
    public function table(string $name, Closure $callback): void {
        $this->build(auto($this->createBluePrint($name), function($table) use($callback) {
            $callback($table);
        }));
    }

    /**
     * @throws \Artificers\Treaties\Database\Driver\Exception
     * @throws Exception
     */
    public function exists(string $table): bool {
        $result = $this->connection->runQuery($this->grammar->compileTableExists(), [$this->connection->getSchema(), $table]);
        return count($result->fetchAllRowsAsNumeric()) > 0;
    }
}