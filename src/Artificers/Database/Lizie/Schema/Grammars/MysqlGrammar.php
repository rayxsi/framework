<?php

namespace Artificers\Database\Lizie\Schema\Grammars;

use Artificers\Database\Lizie\Connections\Connection;
use Artificers\Database\Lizie\Schema\Column;
use Artificers\Database\Lizie\Command;
use Artificers\Database\Lizie\Schema\Exceptions\ForeignKeyException;
use Artificers\Database\Lizie\Schema\Table;

final class MysqlGrammar extends Grammar
{

    /**
     * Burn the SQL that's needed to check if table is exists.
     *
     * @return string
     */
    public function burnTableExists(): string {
        return "SELECT * FROM information_schema.tables WHERE table_schema = ? AND table_name = ? AND table_type = 'BASE TABLE'";
    }

    /**
     *  Burn the SQL that's needed to drop database if exists.
     * @param string $name
     * @return string
     */
    public function burnDropDatabaseIfExists(string $name): string {
        return sprintf("DROP DATABASE IF EXISTS %s", $name);
    }

    /**
     *  Burn the SQL that's needed to create table if not exists.
     * @param Table $table
     * @param Connection $connection
     * @param Command $command
     * @return string
     * @throws ForeignKeyException
     */
    public function burnCreate(Table $table, Connection $connection, Command $command): string {
        return $this->burnCreateTable($table);
    }

    /**
     *  Burn the SQL that's needed to create database if not exists.
     * @param string $name
     * @return string
     */
    public function burnCreateDatabaseIfNotExists(string $name): string {
        return sprintf("CREATE DATABASE IF NOT EXISTS %s", $name);
    }

    /**
     *  Burn the SQL that's needed to use a database.
     * @param string $name
     * @return string
     */
    public function burnUseDatabase(string $name): string {
        return sprintf("USE %s", $name);
    }

    /**
     * Generate SQL for creating table if not exists;
     *
     * @throws ForeignKeyException
     */
    protected function burnCreateTable(Table $table): string {
        return sprintf("CREATE TABLE IF NOT EXISTS %s (%s)", $table->getName(), $this->colMapToString($this->arrangeColumns($table)));
    }

    /**
     * Burn the SQL that's generate not null constraint.
     * @return string
     */
    public function burnNotNullAble(): string {
        return "NOT NULL";
    }

    /**
     * Burn the SQL that's generate null constraint.
     * @return string
     */
    public function burnNullAble(): string {
        return "NULL";
    }

    /**
     * Burn the SQL that's generate auto increment constraint.
     * @return string
     */
    public function burnAutoIncrement(): string {
        return "AUTO_INCREMENT";
    }

    /**
     * Burn the SQL that's generate unique constraint.
     * @return string
     */
    public function burnUnique(): string {
        return "UNIQUE";
    }

    /**
     * Burn the SQL that's needed to create primary key.
     * @param array $columns
     * @return string
     */
    public function burnPrimaryKey(array $columns): string {
        return sprintf("PRIMARY KEY(%s)", $this->colMapToString($columns));
    }

    /**
     * Burn the SQL that's needed to create primary key constraint.
     * @param Table $table
     * @param array $columns
     * @return string
     */
    public function burnPrimaryKeyConstraint(Table $table, array $columns): string {
        return sprintf("CONSTRAINT Rx_Pk_%s PRIMARY KEY (%s)", $table->getName(), $this->colMapToString($columns));
    }

    /**
     *  Burn the SQL that's needed to create foreign key constraint.
     * @param Table $table
     * @param Column $column
     * @return string
     */
    public function burnForeignKey(Table $table, Column $column): string {
        return sprintf("CONSTRAINT Rx_Fk_{$table->getName()}_{$column->getName()} FOREIGN KEY(%s) REFERENCES %s", $column->getName(), $column->getReferences());
    }

    /**
     * Burn the command to enable foreign key constraints.
     *
     * @return string
     */
    public function burnEnableForeignKeyConstraints(): string {
        return 'SET FOREIGN_KEY_CHECKS=1;';
    }

    /**
     * Burn the SQL command to disable foreign key constraints.
     *
     * @return string
     */
    public function burnDisableForeignKeyConstraints(): string {
        return 'SET FOREIGN_KEY_CHECKS=0;';
    }

    /**
     * Burn the SQL command to create default constraints.
     * @param mixed $default
     * @return string
     */
    public function burnDefault(mixed $default): string {
        if (is_string($default)) {
            $default = "'$default'";
        }

        return sprintf("DEFAULT %s", $default);
    }

    /**
     * Burn the SQL command to create check constraints.
     * @param mixed $condition
     * @return string
     */
    public function burnCheck(mixed $condition): string {
        return sprintf("CHECK(%s)", $condition);
    }

    /**
     * Burn the SQL command to create index.
     * @param Table $table
     * @param Connection $connection
     * @param Command $command
     * @return string
     */
    public function burnCreateIndex(Table $table, Connection $connection, Command $command): string {
        $sqlStr = isset($command['unique']) ? $this->uniqueIndex() : "CREATE INDEX Rx_Idx_%s ON %s (%s)";

        return sprintf($sqlStr, ucfirst($table->getName()), $table->getName(), $this->colMapToString($command['columns']));
    }

    /**
     * Burn the SQL command to create unique index.
     * @return string
     */
    private function uniqueIndex(): string {
        return "CREATE UNIQUE INDEX Rx_Idx_%s ON %s (%s)";
    }

    /**
     * Burn the SQL command to drop table.
     * @param Table $table
     * @param Connection $connection
     * @param Command $command
     * @return string
     */
    public function burnDropIfExists(Table $table, Connection $connection, Command $command): string {
        return sprintf("DROP TABLE IF EXISTS %s", $table->getName());
    }

    /**
     * Burn the SQL command to drop primary key constraint.
     * @param Table $table
     * @param Connection $connection
     * @param Command $command
     * @return string
     */
    public function burnDropPrimaryKey(Table $table, Connection $connection, Command $command): string {
        return sprintf("ALTER TABLE %s DROP PRIMARY KEY", $table->getName());
    }

    /**
     * Burn the SQL command to drop foreign key constraint.
     * @param Table $table
     * @param Connection $connection
     * @param Command $command
     * @return string
     */
    public function burnDropForeignKey(Table $table, Connection $connection, Command $command): string {
        return sprintf("ALTER TABLE %s DROP FOREIGN KEY %s", $table->getName(), $command['index']);
    }

    /**
     * Burn the SQL command to drop table column.
     * @param Table $table
     * @param Connection $connection
     * @param Command $command
     * @return string
     */
    public function burnDropColumn(Table $table, Connection $connection, Command $command): string {
        return sprintf("ALTER TABLE %s DROP COLUMN %s", $table->getName(), $command['index']);
    }

    /**
     * Burn the SQL command to drop index.
     * @param Table $table
     * @param Connection $connection
     * @param Command $command
     * @return string
     */
    public function burnDropIndex(Table $table, Connection $connection, Command $command): string {
        return sprintf("ALTER TABLE %s DROP INDEX %s", $table->getName(), $command['index']);
    }

    /**
     * Burn the SQL command to add columns to an existing table.
     * @param Table $table
     * @param Connection $connection
     * @param Command $command
     * @return string
     * @throws ForeignKeyException
     */
    public function burnAddColumn(Table $table, Connection $connection, Command $command): string {
        return sprintf("ALTER TABLE %s ADD COLUMN %s", $table->getName(), $this->colMapToString($this->arrangeColumns($table, $command['columns']), "ADD COLUMN"));
    }

    /**
     * Burn the SQL command to rename an existing column in a table.
     * @param Table $table
     * @param Connection $connection
     * @param Command $command
     * @return string
     */
    public function burnColumnRename(Table $table, Connection $connection, Command $command): string {
        return sprintf("ALTER TABLE {$table->getName()} RENAME COLUMN %s TO %s", $command['old'], $command['new']);
    }

    /**
     * Burn the SQL command to rename an existing table.
     * @param string $old
     * @param string $new
     * @return string
     */
    public function burnTableRename(string $old, string $new): string {
        return sprintf("ALTER TABLE %s RENAME TO %s", $old, $new);
    }

    /**
     * Burn the SQL command to modify an existing column in a table.
     * @param Table $table
     * @param Connection $connection
     * @param Command $command
     * @return string
     * @throws ForeignKeyException
     */
    public function burnChangeColumn(Table $table, Connection $connection, Command $command): string {
        return sprintf("ALTER TABLE {$table->getName()} MODIFY COLUMN %s", $this->colMapToString($this->arrangeColumns($table, $command['columns'])));
    }

    /**
     * Burn the SQL needed to retrieve all table names.
     *
     * @return string
     */
    public function burnGetAllTables(): string {
        return "SHOW FULL TABLES WHERE table_type = 'BASE TABLE'";
    }
}