<?php

namespace Artificers\Database\Lizie\Schema\Grammars;

use Artificers\Database\Lizie\Connections\Connection;
use Artificers\Database\Lizie\Schema\Column;
use Artificers\Database\Lizie\Schema\Command;
use Artificers\Database\Lizie\Schema\Exceptions\ForeignKeyException;
use Artificers\Database\Lizie\Schema\Table;

final class MysqlGrammar extends Grammar
{

    public function compileTableExists(): string {
        return "SELECT * FROM information_schema.tables WHERE table_schema = ? AND table_name = ? AND table_type = 'BASE TABLE'";
    }

    public function compileDropDatabaseIfExists(string $name): string {
        return sprintf("DROP DATABASE IF EXISTS %s", $name);
    }

    /**
     * @throws ForeignKeyException
     */
    public function compileCreate(Table $table, Connection $connection, Command $command): string {
        return $this->compileCreateTable($table);
    }

    public function compileCreateDatabaseIfNotExists(string $name): string
    {
        return sprintf("CREATE DATABASE IF NOT EXISTS %s", $name);
    }

    public function compileUseDatabase(string $name): string
    {
        return sprintf("USE %s", $name);
    }

    /**
     * @throws ForeignKeyException
     */
    protected function compileCreateTable(Table $table): string
    {
        return sprintf("CREATE TABLE IF NOT EXISTS %s (%s)", $table->getName(), $this->colMapToString($this->arrangeColumns($table)));
    }

    public function compileNotNull(): string
    {
        return "NOT NULL";
    }

    public function compileAutoIncrement(): string {
        return "AUTO_INCREMENT";
    }

    public function compileUnique(): string {
        return "UNIQUE";
    }

    public function compilePrimaryKey(array $columns): string {
        return sprintf("PRIMARY KEY(%s)", $this->colMapToString($columns));
    }

    public function compilePrimaryKeyConstraint(Table $table, array $columns): string {
        return sprintf("CONSTRAINT Rx_Pk_%s PRIMARY KEY (%s)", $table->getName(), $this->colMapToString($columns));
    }

    public function compileForeignKey(Table $table, Column $column): string {
        return sprintf("CONSTRAINT Rx_Fk_{$table->getName()}_{$column->getName()} FOREIGN KEY(%s) REFERENCES %s", $column->getName(), $column->getReferences());
    }

    public function compileDefault(mixed $default): string {
        if (is_string($default)) {
            $default = "'$default'";
        }

        return sprintf("DEFAULT %s", $default);
    }

    public function compileCheck(mixed $condition): string {
        return sprintf("CHECK(%s)", $condition);
    }

    public function compileCreateIndex(Table $table, Connection $connection, Command $command): string {
        $sqlStr = isset($command['unique']) ? $this->uniqueIndex() : "CREATE INDEX Rx_Idx_%s ON %s (%s)";

        return sprintf($sqlStr, ucfirst($table->getName()), $table->getName(), $this->colMapToString($command['columns']));
    }

    private function uniqueIndex(): string {
        return "CREATE UNIQUE INDEX Rx_Idx_%s ON %s (%s)";
    }

    public function compileDropIfExists(Table $table, Connection $connection, Command $command): string {
        return sprintf("DROP TABLE IF EXISTS %s", $table->getName());
    }

    public function compileDropPrimaryKey(Table $table, Connection $connection, Command $command): string {
        return sprintf("ALTER TABLE %s DROP PRIMARY KEY", $table->getName());
    }

    public function compileDropForeignKey(Table $table, Connection $connection, Command $command): string {
        return sprintf("ALTER TABLE %s DROP FOREIGN KEY %s", $table->getName(), $command['index']);
    }

    public function compileDropColumn(Table $table, Connection $connection, Command $command): string {
        return sprintf("ALTER TABLE %s DROP COLUMN %s", $table->getName(), $command['index']);
    }

    public function compileDropIndex(Table $table, Connection $connection, Command $command): string {
        return sprintf("ALTER TABLE %s DROP INDEX %s", $table->getName(), $command['index']);
    }
}