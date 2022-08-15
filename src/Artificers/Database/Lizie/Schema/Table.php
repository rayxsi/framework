<?php

namespace Artificers\Database\Lizie\Schema;

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

    public function column(string $name): Column {

        return $this->_createColumn($name);
    }

    public function add(array $columns): void {
        if(empty($this->columns)) {
            $this->columns = $columns;
        }
    }

    protected function _createColumn(string $name): Column {
        return new Column($name);
    }

    public function getColumns(): array {
        return $this->columns;
    }

    public function create(): void {
        $this->addCommand("create");
    }

    private function addCommand(string $name, array $params=[]): void {
        $this->commands[] = $this->createCommand($name, $params);
    }

    private function createCommand(string $name, array $params=[]): Command {
        return new Command(Ary::merge(compact("name"), $params));
    }

    /**
     * @throws Exception
     */
    public function build(Connection $connection, Grammar $grammar): void {
        foreach($this->mapToSql($connection, $grammar) as $sql) {
            $connection->runQuery($sql);
        }
    }

    private function mapToSql(Connection $connection, Grammar $grammar): array {
        $statements = [];

        foreach($this->commands as $command) {
            $method = "compile".ucfirst($command['name']);

            if(method_exists($grammar, $method)) {
                if(!is_null($sql = $grammar->$method($this, $connection, $command))) {
                    $statements = Ary::merge($statements, (array)$sql);
                }
            }
        }

        return $statements;
    }

    public function dropIfExists(): void {
        $this->addCommand("dropIfExists");
    }

    public function dropPrimaryKey(): void {
        $this->addCommand("dropPrimaryKey");
    }

    public function dropForeignKey(string $identifier): void {
        $this->addCommand("dropForeignKey", compact("identifier"));
    }

    public function getName(): string {
        return $this->name;
    }
}