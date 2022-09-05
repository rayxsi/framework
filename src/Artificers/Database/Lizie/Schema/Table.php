<?php

namespace Artificers\Database\Lizie\Schema;

use Artificers\Database\Lizie\Command;
use Artificers\Database\Lizie\Connections\Connection;
use Artificers\Database\Lizie\Driver\Exception;
use Artificers\Database\Lizie\Schema\Grammars\Grammar;
use Artificers\Utilities\Ary;

class Table {

    protected string $name;

    private array $columns = [];

    private array $commands = [];

    public function __construct(string $name) {
        $this->name = $name;
    }

    /**
     * Create and add column.
     * @param string $name
     * @return Column
     */
    public function column(string $name): Column {
        $this->columns[] = $column = $this->_createColumn($name);
        return $column;
    }

    /**
     * Create instance of Column.
     * @param string $name
     * @return Column
     */
    protected function _createColumn(string $name): Column {
        return new Column($name);
    }

    /**
     * Return all the column of existing table.
     *
     * @return array
     */
    public function getColumns(): array {
        return $this->columns;
    }

    /**
     * Add create table command.
     * @return void
     */
    public function create(): void {
        $this->addCommand("create");
    }

    /**
     * Create and add command.
     * @param string $name
     * @param array $params
     * @return void
     */
    private function addCommand(string $name, array $params=[]): void {
        $this->commands[] = $this->createCommand($name, $params);
    }

    /**
     * Create instance of Command class.
     * @param string $name
     * @param array $params
     * @return Command
     */
    private function createCommand(string $name, array $params=[]): Command {
        return new Command(Ary::merge(compact("name"), $params));
    }

    /**
     * Run and build all the SQL command.
     * @param Connection $connection
     * @param Grammar $grammar
     * @throws Exception
     */
    public function build(Connection $connection, Grammar $grammar): void {
        foreach($this->mapToSql($connection, $grammar) as $sql) {
            $connection->runQuery(trim($sql));
        }
    }

    /**
     * Check commands and generates the appropriate SQL commands with the help of grammar.
     * @param Connection $connection
     * @param Grammar $grammar
     * @return array
     */
    private function mapToSql(Connection $connection, Grammar $grammar): array {
        $this->checkingProcessedCmd();

        $statements = [];

        foreach($this->commands as $command) {
            $method = "burn".ucfirst($command['name']);

            if(method_exists($grammar, $method)) {
                if(!is_null($sql = $grammar->$method($this, $connection, $command))) {
                    $statements = Ary::merge($statements, (array)$sql);
                }
            }
        }

        return $statements;
    }

    /**
     * Checks the columns that should be added or modified to existing table.
     * @return void
     */
    private function checkingProcessedCmd(): void {
        if(count($columns = $this->getAddedColumns()) > 0 && !$this->creating()) {
            array_unshift($this->commands, $this->createCommand('addColumn', compact('columns')));
        }

        if(count($columns = $this->getChangedColumns()) > 0 && !$this->creating()) {
            array_unshift($this->commands, $this->createCommand('changeColumn', compact('columns')));
        }
    }

    /**
     * Add create index command.
     * @param ...$columns
     * @return void
     */
    public function index(...$columns): void {
        $this->addCommand("createIndex", compact('columns'));
    }

    /**
     * Add create unique index command.
     * @param ...$columns
     * @return void
     */
    public function uniqueIndex(...$columns): void {
        $unique = true;

        $this->addCommand("createIndex", compact('columns', 'unique'));
    }

    /**
     * Add drop table if exists command.
     * @return void
     */
    public function dropIfExists(): void {
        $this->addCommand("dropIfExists");
    }

    /**
     * Add drop primary key constraint command.
     * @return void
     */
    public function dropPrimaryKey(): void {
        $this->addCommand("dropPrimaryKey");
    }

    /**
     * Add drop foreign key constraint command.
     * @param string $index
     * @return void
     */
    public function dropForeignKey(string $index): void {
        $this->addCommand("dropForeignKey", compact("index"));
    }

    /**
     * Add drop column command.
     * @param string $index
     * @return void
     */
    public function dropColumn(string $index): void {
        $this->addCommand("dropColumn", compact("index"));
    }

    /**
     * Add drop unique command.
     * @param string $index
     * @return void
     */
    public function dropUnique(string $index): void {
        $this->dropIndex($index);
    }

    /**
     * Add drop index command.
     * @param string $index
     * @return void
     */
    public function dropIndex(string $index): void {
        $this->addCommand("dropIndex", compact('index'));
    }

    /**
     * Get the current table name.
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * Check if the command is create command or not.
     * @return bool
     */
    private function creating(): bool {
        foreach($this->commands as $command) {
            if($command['name'] === 'create') return true;
        }

        return false;
    }

    /**
     * Rename existing table column.
     *
     * @param string $old
     * @param string $new
     * @return void
     */
    public function rename(string $old, string $new): void {
        $this->addCommand('columnRename', compact('old', 'new'));
    }

    /**
     * Check the columns that is marked as change and return them.
     * @return array
     */
    public function getChangedColumns(): array {
        return Ary::filter($this->columns, function($column) {
            return $column->isMarkedAsChange();
        });
    }

    /**
     * Check the columns that is not marked as change and return them.
     * @return array
     */
    public function getAddedColumns(): array {
        return Ary::filter($this->columns, function($column) {
            return !$column->isMarkedAsChange();
        });
    }
}