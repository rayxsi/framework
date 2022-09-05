<?php

namespace Artificers\Database\Lizie\Query;

use Artificers\Database\Lizie\Command;
use Artificers\Database\Lizie\Connections\Connection;
use Artificers\Database\Lizie\Driver\Exception;
use Artificers\Database\Lizie\Query\Grammars\Grammar;
use Artificers\Utilities\Ary;
use LogicException;

class Builder {
    private Connection $connection;
    private Grammar $grammar;
    private ?Command $command = null;

    private array $placeHolderValues = [];
    private array $boat;

    public function __construct(Connection $connection) {
        $this->connection = $connection;
        $this->grammar = $connection->getQueryGrammar();
    }

    /**
     * Execute the sql command.
     * @throws Exception
     */
    public function run(): bool|array|string {
        $outcome = $this->_build(1);
        $this->flushPv();

        return $outcome;
    }

    /**
     * SQL SELECT clause.
     * @param ...$columns
     * @return $this
     */
    public function select(...$columns): static {
        $this->addCommand("select", compact("columns"));
        return $this;
    }

    /**
     * Return sql.
     * @return string
     */
    public function toSQL(): string {
        return $this->_build(0);
    }

    /**
     * SQL INSERT clause.
     * @param array $data
     * @return $this
     */
    public function insert(array $data): static {
        $columns = array_keys($data);
        $this->placeHolderValues = array_values($data);
        $namePlaceHolder = $this->transformIntoPositionalPlaceHolder(count($columns));

        $this->addCommand('insert', compact('columns', 'namePlaceHolder'));

        return $this;
    }

    /**
     * SQL INSERT INTO SELECT clause.
     * @param ...$columns
     * @return $this
     */
    public function insertWithCpy(...$columns): static {
        $this->addCommand("insertWithCpy", compact('columns'));
        return $this;
    }

    /**
     * Copy the sql query result. It works with insertWithCpy method.
     * @param string $sql
     * @return $this
     */
    public function copy(string $sql): static {
        $this->boat[] = $sql;
        return $this;
    }

    /**
     * SQL UPDATE clause.
     * @param array $data
     * @return $this
     */
    public function update(array $data): static {
        $this->placeHolderValues = array_values($data);

        $columnWithPlaceHolder = [];
        array_walk($data, function($value, $key)use(&$columnWithPlaceHolder){
            $columnWithPlaceHolder[] = "{$key}=?";
        });

        $this->addCommand("update", compact('columnWithPlaceHolder'));

        return $this;
    }

    private function transformKeyToNamedPlaceHolderAndBindValue(array $data): void {
        array_walk($data, function($value, $key) {
            $this->placeHolderValues[":{$key}"] = $value;
        });
    }

    /**
     * Transform into positional placeholder according to number target. 
     * @param int $number
     * @return string
     */
    private function transformIntoPositionalPlaceHolder(int $number): string {
        return trim(str_repeat("?, ", $number), ", ");
    }

    /**
     * SQL DELETE clause.
     * @return $this
     */
    public function delete(): static {
        $this->addCommand("delete");

        return $this;
    }

    /**
     * Build the sql and execute it.
     * @throws Exception
     * @throws \Exception
     */
    protected function _build(int $executionFlag=0): bool|array|string {
       $sql = $this->mapToSql();
       if($executionFlag === 0) return $sql;

       $result = $this->connection->runQuery(trim($sql), $this->placeHolderValues);

        return match ($this->command['name']) {
            'update', "delete", 'insert', 'insertWithCpy' => $result->getExecStatus(),
            default => $result->fetchAllRowsAsObject(),
        };
    }

    /**
     * Map to sql according to command name.
     * @return string
     */
    protected function mapToSql(): string {
        if(!is_null($this->command)) {
            $method = "burn".ucfirst($this->command['name']);

            if(method_exists($this->grammar, $method)) {
                if(!is_null($sql = $this->grammar->$method($this->connection, $this->command, $this->boat))) {
                    return trim($sql);
                }
            }
        }

        throw new LogicException("Command not found");
    }

    public function getValues(): array {
        return $this->placeHolderValues;
    }

    /**
     * Create and add command.
     * @param string $name
     * @param array $params
     * @return void
     */
    private function addCommand(string $name, array $params=[]): void {
        $this->command = $this->createCommand($name, $params);
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
     * Extract the value and replace with positional mark.
     *
     * @param array $conditions
     * @return void
     */
    private function extractor(array & $conditions): void {
        array_walk($conditions, function(&$condition) {
            if(isset($condition[2])) {
                $this->placeHolderValues[] = $condition[2];
                $condition[2] = "?";

                /*if(preg_match("/(?<=\.)(\w+)/", $condition[0], $matches)) {
                    $this->placeHolderValues[":{$matches[1]}"] = $condition[2];
                    $condition[2] = ":{$matches[1]}";
                }*/
            }

            if(isset($condition[1]) && $condition[1] === "!=") {
                $condition[0] = "NOT {$condition[0]}";
                $condition[1] = "=";
            }
        });
    }

    /**
     * SQL WHERE clause with AND conjunction.
     * @param ...$condition
     * @return $this
     */
    public function where(...$condition): static {
        if(!is_array($condition[0])) $condition = [$condition];
        $this->extractor($condition);
        $this->boat[] = $this->grammar->burnWhere($condition);

        return $this;
    }

    /**
     * SQL HAVING clause with AND conjunction.
     * @param ...$condition
     * @return $this
     */
    public function having(...$condition): static {
        if(!is_array($condition[0])) $condition = [$condition];
        $this->extractor($condition);
        $this->boat[] = $this->grammar->burnHaving($condition);

        return $this;
    }

    /**
     * SQL HAVING clause with AND conjunction.
     * @param ...$condition
     * @return $this
     */
    public function orHaving(...$condition): static {
        if(!is_array($condition[0])) $condition = [$condition];
        $this->extractor($condition);
        $this->boat[] = $this->grammar->burnOrHaving($condition);

        return $this;
    }
    
    /**
     * SQL WHERE clause with OR conjunction.
     * @param ...$condition
     * @return $this
     */
    public function orWhere(...$condition): static {
        if(!is_array($condition[0])) $condition = [$condition];
        $this->extractor($condition);
        $this->boat[] = $this->grammar->burnOrWhere($condition);

        return $this;
    }

    /**
     * SQL AND clause.
     * @param ...$condition
     * @return $this
     */
    public function and(...$condition): static {
        if(!is_array($condition[0])) $condition = [$condition];
        $this->extractor($condition);
        $this->boat[] = $this->grammar->burnAnd($condition);

        return $this;
    }

    /**
     * SQL OR clause.
     * @param ...$condition
     * @return $this
     */
    public function or(...$condition): static {
        if(!is_array($condition[0])) $condition = [$condition];
        $this->extractor($condition);
        $this->boat[] = $this->grammar->burnOr($condition);

        return $this;
    }

    /**
     * Initial point to build query in a specific table.
     * @param string $table
     * @param string $alias
     * @return $this
     */
    public function from(string $table, string $alias=''): static {
        $this->flushBoat();
        $this->boat['table'] = $table;
        $this->boat['as'] = $alias;

        return $this;
    }

    /**
     * Initial point to build query in a specific table.
     * @param string $table
     * @param string $alias
     * @return $this
     */
    public function table(string $table, string $alias=''): static {
        $this->flushBoat();
        $this->boat['table'] = $table;
        $this->boat['as'] = $alias;

        return $this;
    }

    /**
     * SQL ORDER BY clause.
     * @param array $modifiers
     * @return $this
     */
    public function orderBy(array $modifiers): static {
        $bindWithKeysAndValues = [];
        foreach($modifiers as $key=>$value) {
            $bindWithKeysAndValues[] = $key." ".strtoupper($value);
        }
        $this->boat[] = $this->grammar->burnOrderBy($bindWithKeysAndValues);
        return $this;
    }

    /**
     * SQL GROUP BY clause.
     * @param ...$columns
     * @return $this
     */
    public function groupBy(...$columns): static {
        $this->boat[] = $this->grammar->burnGroupBy($columns);
        return $this;
    }

    /**
     * SQL LIMIT clause.
     * @param int $limit
     * @return $this
     */
    public function limit(int $limit): static {
        $this->boat[] = $this->grammar->burnLimit($limit);
        return $this;
    }

    /**
     * SQL WHERE BETWEEN clause.
     * @param string $column
     * @param array $limits
     * @return $this
     */
    public function whereBetween(string $column, array $limits): static {
        $this->boat[] = $this->grammar->burnWhereBetween($column, $limits);

        return $this;
    }

    /**
     * SQL WHERE NOT BETWEEN clause.
     * @param string $column
     * @param array $limits
     * @return $this
     */
    public function whereNotBetween(string $column, array $limits): static {
        $this->boat[] = $this->grammar->burnWhereNotBetween($column, $limits);
        return $this;
    }

    /**
     * SQL WHERE IN clause.
     * @param string $column
     * @param array $values
     * @return $this
     */
    public function whereIn(string $column, array $values): static {
        $this->boat[] = $this->grammar->burnWhereIn($column, $values);
        return $this;
    }

    /**
     * SQL WHERE NOT IN clause.
     * @param string $column
     * @param array $values
     * @return $this
     */
    public function whereNotIn(string $column, array $values): static {
        $this->boat[] = $this->grammar->burnWhereNotIn($column, $values);
        return $this;
    }

    /**
     * SQL WHERE LIKE clause.
     * @param string $column
     * @param string $pattern
     * @return $this
     */
    public function whereLike(string $column, string $pattern): static {
        $this->boat[] = $this->grammar->burnWhereLike($column, $pattern);
        return $this;
    }

    /**
     * SQL WHERE NOT LIKE clause.
     * @param string $column
     * @param string $pattern
     * @return $this
     */
    public function whereNotLike(string $column, string $pattern): static {
        $this->boat[] = $this->grammar->burnWhereNotLike($column, $pattern);
        return $this;
    }

    /**
     * SQL OR LIKE clause.
     * @param string $column
     * @param $pattern
     * @return $this
     */
    public function orLike(string $column, $pattern): static {
        $this->boat[] = $this->grammar->burnOrLike($column, $pattern);
        return $this;
    }

    /**
     * SQL OR NOT LIKE clause.
     * @param string $column
     * @param $pattern
     * @return $this
     */
    public function orNotLike(string $column, $pattern): static {
        $this->boat[] = $this->grammar->burnOrNotLike($column, $pattern);
        return $this;
    }

    /**
     * SQL AND LIKE clause.
     * @param string $column
     * @param $pattern
     * @return $this
     */
    public function andLike(string $column, $pattern): static {
        $this->boat[] = $this->grammar->burnAndLike($column, $pattern);
        return $this;
    }

    /**
     * SQL AND NOT LIKE clause.
     * @param string $column
     * @param $pattern
     * @return $this
     */
    public function andNotLike(string $column, $pattern): static {
        $this->boat[] = $this->grammar->burnAndNotLike($column, $pattern);
        return $this;
    }

    /**
     * SQL INNER JOIN clause.
     * @param string $table
     * @param string $condition
     * @param string $alias
     * @return $this
     */
    public function innerJoin(string $table, string $condition, string $alias=""): static {
        $this->boat[] = $this->grammar->burnInnerJoin($table, $condition, $alias);
        return $this;
    }

    /**
     * SQL LEFT JOIN clause.
     * @param string $table
     * @param string $condition
     * @param string $alias
     * @return $this
     */
    public function leftJoin(string $table, string $condition, string $alias=""): static {
        $this->boat[] = $this->grammar->burnLeftJoin($table, $condition, $alias);
        return $this;
    }

    /**
     * SQL RIGHT JOIN clause.
     * @param string $table
     * @param string $condition
     * @param string $alias
     * @return $this
     */
    public function rightJoin(string $table, string $condition, string $alias=""): static {
        $this->boat[] = $this->grammar->burnRightJoin($table, $condition, $alias);
        return $this;
    }

    /**
     * SQL FULL OUTER JOIN clause.
     *
     * @param string $table
     * @param string $condition
     * @param string $alias
     * @return $this
     */
    public function fullJoin(string $table, string $condition, string $alias=""): static {
        $this->boat[] = $this->grammar->burnFullJoin($table, $condition, $alias);
        return $this;
    }

    /**
     * SQL UNION clause.
     *
     * @param string $sql
     * @return $this
     */
    public function union(string $sql): static {
        $this->boat[] = $this->grammar->burnUnion($sql);
        return $this;
    }

    /**
     * SQL UNION ALL clause.
     *
     * @param string $sql
     * @return $this
     */
    public function unionAll(string $sql): static {
        $this->boat[] = $this->grammar->burnUnionAll($sql);
        return $this;
    }

    /**
     * Clean boat.
     *
     * @return void
     */
    private function flushBoat(): void {
        $this->boat = [];
    }

    /**
     * Clean place holder values array.
     *
     * @return void
     */
    private function flushPv(): void {
        $this->placeHolderValues = [];
    }
}