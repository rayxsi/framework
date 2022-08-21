<?php

namespace Artificers\Database\Lizie\Schema\Grammars;

use Artificers\Database\Lizie\Connections\Connection;
use Artificers\Database\Lizie\Schema\Column;
use Artificers\Database\Lizie\Schema\Command;
use Artificers\Database\Lizie\Schema\Exceptions\ForeignKeyException;
use Artificers\Database\Lizie\Schema\Table;

final class MysqlGrammar extends Grammar
{

    /**
     * Compile the SQL that's needed to check if table is exists.
     *
     * @return string
     */
    public function compileTableExists(): string {
        return "SELECT * FROM information_schema.tables WHERE table_schema = ? AND table_name = ? AND table_type = 'BASE TABLE'";
    }

    /**
     *  Compile the SQL that's needed to drop database if exists.
     * @param string $name
     * @return string
     */
    public function compileDropDatabaseIfExists(string $name): string {
        return sprintf("DROP DATABASE IF EXISTS %s", $name);
    }

    /**
     *  Compile the SQL that's needed to create table if not exists.
     * @param Table $table
     * @param Connection $connection
     * @param Command $command
     * @return string
     * @throws ForeignKeyException
     */
    public function compileCreate(Table $table, Connection $connection, Command $command): string {
        return $this->compileCreateTable($table);
    }

    /**
     *  Compile the SQL that's needed to create database if not exists.
     * @param string $name
     * @return string
     */
    public function compileCreateDatabaseIfNotExists(string $name): string {
        return sprintf("CREATE DATABASE IF NOT EXISTS %s", $name);
    }

    /**
     *  Compile the SQL that's needed to use a database.
     * @param string $name
     * @return string
     */
    public function compileUseDatabase(string $name): string {
        return sprintf("USE %s", $name);
    }

    /**
     * Generate SQL for creating table if not exists;
     *
     * @throws ForeignKeyException
     */
    protected function compileCreateTable(Table $table): string {
        return sprintf("CREATE TABLE IF NOT EXISTS %s (%s)", $table->getName(), $this->colMapToString($this->arrangeColumns($table)));
    }

    /**
     * Compile the SQL that's generate not null constraint.
     * @return string
     */
    public function compileNotNullAble(): string {
        return "NOT NULL";
    }

    /**
     * Compile the SQL that's generate null constraint.
     * @return string
     */
    public function compileNullAble(): string {
        return "NULL";
    }

    /**
     * Compile the SQL that's generate auto increment constraint.
     * @return string
     */
    public function compileAutoIncrement(): string {
        return "AUTO_INCREMENT";
    }

    /**
     * Compile the SQL that's generate unique constraint.
     * @return string
     */
    public function compileUnique(): string {
        return "UNIQUE";
    }

    /**
     * Compile the SQL that's needed to create primary key.
     * @param array $columns
     * @return string
     */
    public function compilePrimaryKey(array $columns): string {
        return sprintf("PRIMARY KEY(%s)", $this->colMapToString($columns));
    }

    /**
     * Compile the SQL that's needed to create primary key constraint.
     * @param Table $table
     * @param array $columns
     * @return string
     */
    public function compilePrimaryKeyConstraint(Table $table, array $columns): string {
        return sprintf("CONSTRAINT Rx_Pk_%s PRIMARY KEY (%s)", $table->getName(), $this->colMapToString($columns));
    }

    /**
     *  Compile the SQL that's needed to create foreign key constraint.
     * @param Table $table
     * @param Column $column
     * @return string
     */
    public function compileForeignKey(Table $table, Column $column): string {
        return sprintf("CONSTRAINT Rx_Fk_{$table->getName()}_{$column->getName()} FOREIGN KEY(%s) REFERENCES %s", $column->getName(), $column->getReferences());
    }

    /**
     * Compile the command to enable foreign key constraints.
     *
     * @return string
     */
    public function compileEnableForeignKeyConstraints(): string {
        return 'SET FOREIGN_KEY_CHECKS=1;';
    }

    /**
     * Compile the SQL command to disable foreign key constraints.
     *
     * @return string
     */
    public function compileDisableForeignKeyConstraints(): string {
        return 'SET FOREIGN_KEY_CHECKS=0;';
    }

    /**
     * Compile the SQL command to create default constraints.
     * @param mixed $default
     * @return string
     */
    public function compileDefault(mixed $default): string {
        if (is_string($default)) {
            $default = "'$default'";
        }

        return sprintf("DEFAULT %s", $default);
    }

    /**
     * Compile the SQL command to create check constraints.
     * @param mixed $condition
     * @return string
     */
    public function compileCheck(mixed $condition): string {
        return sprintf("CHECK(%s)", $condition);
    }

    /**
     * Compile the SQL command to create index.
     * @param Table $table
     * @param Connection $connection
     * @param Command $command
     * @return string
     */
    public function compileCreateIndex(Table $table, Connection $connection, Command $command): string {
        $sqlStr = isset($command['unique']) ? $this->uniqueIndex() : "CREATE INDEX Rx_Idx_%s ON %s (%s)";

        return sprintf($sqlStr, ucfirst($table->getName()), $table->getName(), $this->colMapToString($command['columns']));
    }

    /**
     * Compile the SQL command to create unique index.
     * @return string
     */
    private function uniqueIndex(): string {
        return "CREATE UNIQUE INDEX Rx_Idx_%s ON %s (%s)";
    }

    /**
     * Compile the SQL command to drop table.
     * @param Table $table
     * @param Connection $connection
     * @param Command $command
     * @return string
     */
    public function compileDropIfExists(Table $table, Connection $connection, Command $command): string {
        return sprintf("DROP TABLE IF EXISTS %s", $table->getName());
    }

    /**
     * Compile the SQL command to drop primary key constraint.
     * @param Table $table
     * @param Connection $connection
     * @param Command $command
     * @return string
     */
    public function compileDropPrimaryKey(Table $table, Connection $connection, Command $command): string {
        return sprintf("ALTER TABLE %s DROP PRIMARY KEY", $table->getName());
    }

    /**
     * Compile the SQL command to drop foreign key constraint.
     * @param Table $table
     * @param Connection $connection
     * @param Command $command
     * @return string
     */
    public function compileDropForeignKey(Table $table, Connection $connection, Command $command): string {
        return sprintf("ALTER TABLE %s DROP FOREIGN KEY %s", $table->getName(), $command['index']);
    }

    /**
     * Compile the SQL command to drop table column.
     * @param Table $table
     * @param Connection $connection
     * @param Command $command
     * @return string
     */
    public function compileDropColumn(Table $table, Connection $connection, Command $command): string {
        return sprintf("ALTER TABLE %s DROP COLUMN %s", $table->getName(), $command['index']);
    }

    /**
     * Compile the SQL command to drop index.
     * @param Table $table
     * @param Connection $connection
     * @param Command $command
     * @return string
     */
    public function compileDropIndex(Table $table, Connection $connection, Command $command): string {
        return sprintf("ALTER TABLE %s DROP INDEX %s", $table->getName(), $command['index']);
    }

    /**
     * Compile the SQL command to add columns to an existing table.
     * @param Table $table
     * @param Connection $connection
     * @param Command $command
     * @return string
     * @throws ForeignKeyException
     */
    public function compileAddColumn(Table $table, Connection $connection, Command $command): string {
        return sprintf("ALTER TABLE %s ADD COLUMN %s", $table->getName(), $this->colMapToString($this->arrangeColumns($table, $command['columns']), "ADD COLUMN"));
    }

    /**
     * Compile the SQL command to rename an existing column in a table.
     * @param Table $table
     * @param Connection $connection
     * @param Command $command
     * @return string
     */
    public function compileColumnRename(Table $table, Connection $connection, Command $command): string {
        return sprintf("ALTER TABLE {$table->getName()} RENAME COLUMN %s TO %s", $command['old'], $command['new']);
    }

    /**
     * Compile the SQL command to rename an existing table.
     * @param string $old
     * @param string $new
     * @return string
     */
    public function compileTableRename(string $old, string $new): string {
        return sprintf("ALTER TABLE %s RENAME TO %s", $old, $new);
    }

    /**
     * Compile the SQL command to modify an existing column in a table.
     * @param Table $table
     * @param Connection $connection
     * @param Command $command
     * @return string
     * @throws ForeignKeyException
     */
    public function compileChangeColumn(Table $table, Connection $connection, Command $command): string {
        return sprintf("ALTER TABLE {$table->getName()} MODIFY COLUMN %s", $this->colMapToString($this->arrangeColumns($table, $command['columns'])));
    }

    /**
     * Compile the SQL needed to retrieve all table names.
     *
     * @return string
     */
    public function compileGetAllTables(): string {
        return "SHOW FULL TABLES WHERE table_type = 'BASE TABLE'";
    }
}