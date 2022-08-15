<?php

namespace Artificers\Database\Lizie\Schema\Grammars;

use Artificers\Database\Lizie\Connections\Connection;
use Artificers\Database\Lizie\Schema\Column;
use Artificers\Database\Lizie\Schema\Command;
use Artificers\Database\Lizie\Schema\Exceptions\ForeignKeyException;
use Artificers\Database\Lizie\Schema\Table;

final class MysqlGrammar extends Grammar {

    public function compileTableExists(): string {
        return "SELECT * FROM information_schema.tables WHERE table_schema = ? AND table_name = ? AND table_type = 'BASE TABLE'";
    }
    /**
     * @throws ForeignKeyException
     */
    public function compileCreate(Table $table, Connection $connection, Command $command): string {
        return $this->compileCreateTable($table);
    }

    /**
     * @throws ForeignKeyException
     */
    protected function compileCreateTable(Table $table): string {
        return sprintf("CREATE TABLE IF NOT EXISTS %s (%s)", $table->getName(), $this->colMapToString($this->arrangeColumns($table)));
    }

    public function compileNotNull(): string {
        return "NOT NULL";
    }

    public function compileAutoIncrement(): string {
        return "AUTO_INCREMENT";
    }

    public function compileUnique(): string {
        return "UNIQUE";
    }

    public function compilePrimaryKey(string $column): string {
        return sprintf("PRIMARY KEY(%s)", $column);
    }

    public function compilePrimaryKeyConstraint(string $tableName, string $columns): string {
        return sprintf("CONSTRAINT Rx_Pk_{$tableName} PRIMARY KEY (%s)", $columns);
    }

    public function compileForeignKey(Table $table, Column $column): string {
        return sprintf("CONSTRAINT Rx_Fk_{$table->getName()}_{$column->getName()} FOREIGN KEY(%s) REFERENCES %s", $column->getName(), $column->getReferences());
    }

    public function compileDefault(mixed $default): string {
        if(is_string($default)) {
            $default = "'$default'";
        }

        return sprintf("DEFAULT %s", $default);
    }

    public function compileCheck(mixed $condition): string {
        return sprintf("CHECK(%s)", $condition);
    }

    public function compileDropIfExists(Table $table, Connection $connection, Command $command): string {
        return sprintf("DROP TABLE IF EXISTS %s", $table->getName());
    }

    public function compileDropPrimaryKey(Table $table, Connection $connection, Command $command): string {
        return sprintf("ALTER TABLE %s DROP PRIMARY KEY", $table->getName());
    }

    public function compileDropForeignKey(Table $table, Connection $connection, Command $command): string {
        return sprintf("ALTER TABLE %s DROP FOREIGN KEY %s", $table->getName(), $command['identifier']);
    }
}