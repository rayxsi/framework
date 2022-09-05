<?php

namespace Artificers\Database\Lizie\Query\Grammars;

use Artificers\Database\Lizie\Command;
use Artificers\Database\Lizie\Connections\Connection;
use Artificers\Utilities\Ary;

final class MysqlGrammar extends Grammar {
    /**
     * Burn the SQL SELECT statement.
     * @param Connection $connection
     * @param Command $command
     * @param array $boat
     * @return string
     */
    public function burnSelect(Connection $connection, Command $command, array $boat): string {
        $tableProps = $this->extractTableAndAlias($boat);

        return sprintf("SELECT %s FROM %s %s %s ", $this->_mapToString($command['columns'], ', '), $tableProps["name"], $tableProps["alias"], $this->_mapToString($boat));
    }

    /**
     * Burn the SQL INSERT statement.
     * @param Connection $connection
     * @param Command $command
     * @param array $boat
     * @return string
     */
    public function burnInsert(Connection $connection, Command $command, array $boat): string {
        $tableProps = $this->extractTableAndAlias($boat);

        return sprintf("INSERT INTO %s (%s) VALUES (%s)", $tableProps["name"], $this->_mapToString($command['columns'], ', '), $command['namePlaceHolder']);
    }

    /**
     * Burn the SQL INSERT INTO SELECT statement.
     * @param Connection $connection
     * @param Command $command
     * @param array $boat
     * @return string
     */
    public function burnInsertWithCpy(Connection $connection, Command $command, array $boat): string {
        $tableProps = $this->extractTableAndAlias($boat);
        return sprintf("INSERT INTO %s (%s) %s ", $tableProps['name'], $this->_mapToString($command['columns'], ', '), $this->_mapToString($boat));
    }

    /**
     * Burn the SQL UPDATE statement.
     * @param Connection $connection
     * @param Command $command
     * @param array $boat
     * @return string
     */
    public function burnUpdate(Connection $connection, Command $command, array $boat): string {
        $tableProps = $this->extractTableAndAlias($boat);

        return sprintf("UPDATE %s SET %s %s", $tableProps["name"], $this->_mapToString($command['columnWithPlaceHolder'], ', '), $this->_mapToString($boat));
    }

    /**
     * Burn the SQL DELETE statement.
     * @param Connection $connection
     * @param Command $command
     * @param array $boat
     * @return string
     */
    public function burnDelete(Connection $connection, Command $command, array $boat): string {
        $tableProps = $this->extractTableAndAlias($boat);

        return sprintf("DELETE FROM %s %s ", $tableProps["name"], $this->_mapToString($boat));
    }

    /**
     * Burn the SQL HAVING clause with AND conjunction.
     * @param array $havingClauses
     * @return string
     */
    public function burnHaving(array $havingClauses): string {
        $conditions = $this->_prepareClause($havingClauses);
        $preparedAndClause = implode(" AND ", $conditions);

        return "HAVING {$preparedAndClause}";
    }

    /**
     * Burn the SQL HAVING clause with OR conjunction.
     * @param array $havingClauses
     * @return string
     */
    public function burnOrHaving(array $havingClauses): string {
        $conditions = $this->_prepareClause($havingClauses);
        $preparedAndClause = implode(" OR ", $conditions);

        return "HAVING {$preparedAndClause}";
    }

    /**
     * Burn the SQL WHERE clause with AND conjunction.
     * @param array $whereClauses
     * @return string
     */
    public function burnWhere(array $whereClauses): string {
        $conditions = $this->_prepareClause($whereClauses);
        $preparedAndClause = implode(" AND ", $conditions);

        return "WHERE {$preparedAndClause} ";
    }

    /**
     * Burn the SQL WHERE clause with OR conjunction.
     * @param array $whereClauses
     * @return string
     */
    public function burnOrWhere(array $whereClauses): string {
        $conditions = $this->_prepareClause($whereClauses);
        $preparedAndClause = implode(" OR ", $conditions);

        return "WHERE {$preparedAndClause} ";
    }

    /**
     * Burn the SQL AND clause with AND conjunction.
     * @param array $andClauses
     * @return string
     */
    public function burnAnd(array $andClauses): string {
        $conditions = $this->_prepareClause($andClauses);
        $preparedAndClause = implode(" AND ", $conditions);

        return count($conditions) === 1 ? "AND {$preparedAndClause} " : "AND ({$preparedAndClause}) ";
    }

    /**
     * Burn the SQL OR clause with OR conjunction.
     * @param array $orClauses
     * @return string
     */
    public function burnOr(array $orClauses): string {
        $conditions = $this->_prepareClause($orClauses);
        $preparedAndClause = implode(" OR ", $conditions);

        return count($conditions) === 1 ? "OR {$preparedAndClause} " : "OR ({$preparedAndClause}) ";
    }

    /**
     * Burn the SQL NOT clause.
     * @param array $notClauses
     * @return string
     */
    public function burnNot(array $notClauses): string {
        $conditions = $this->_prepareClause($notClauses);

        return "NOT {$conditions[0]}";
    }

    /**
     * Burn the SQL ORDER BY clause.
     * @param array $modifiers
     * @return string
     */
    public function burnOrderBy(array $modifiers): string {
        return sprintf("ORDER BY %s ", $this->_mapToString($modifiers, ', '));
    }

    /**
     * Burn the SQL GROUP BY clause.
     * @param array $columns
     * @return string
     */
    public function burnGroupBy(array $columns): string {
        return sprintf("GROUP BY %s ", $this->_mapToString($columns, ", "));
    }

    /**
     * Burn the SQL LIMIT clause.
     * @param int $limit
     * @return string
     */
    public function burnLimit(int $limit): string {
        return sprintf("LIMIT %s ", $limit);
    }

    /**
     * Burn the SQL WHERE BETWEEN clause.
     * @param string $column
     * @param array $limits
     * @return string
     */
    public function burnWhereBetween(string $column, array $limits): string {
        return sprintf("WHERE %s BETWEEN %s AND %s ", $column, $limits[0], $limits[1]);
    }

    /**
     * Burn the SQL WHERE NOT BETWEEN clause.
     * @param string $column
     * @param array $limits
     * @return string
     */
    public function burnWhereNotBetween(string $column, array $limits): string {
        return sprintf("WHERE %s NOT BETWEEN %s AND %s ", $column, $limits[0], $limits[1]);
    }

    /**
     * Burn the SQL WHERE IN clause.
     * @param string $column
     * @param array $values
     * @return string
     */
    public function burnWhereIn(string $column, array $values): string {
        return sprintf("WHERE %s IN (%s) ", $column, $this->_mapToString($values, ", "));
    }

    /**
     * Burn the SQL WHERE NOT IN clause.
     * @param string $column
     * @param array $values
     * @return string
     */
    public function burnWhereNotIn(string $column, array $values): string {
        return sprintf("WHERE %s NOT IN (%s) ", $column, $this->_mapToString($values, ", "));
    }

    /**
     * Burn the SQL WHERE LIKE clause.
     * @param string $column
     * @param string $pattern
     * @return string
     */
    public function burnWhereLike(string $column, string $pattern): string {
        return sprintf("WHERE %s LIKE '%s' ", $column, $pattern);
    }

    /**
     * Burn the SQL WHERE NOT LIKE clause.
     * @param string $column
     * @param string $pattern
     * @return string
     */
    public function burnWhereNotLike(string $column, string $pattern): string {
        return sprintf("WHERE %s NOT LIKE '%s' ", $column, $pattern);
    }

    /**
     * Burn the SQL OR LIKE clause.
     * @param string $column
     * @param string $pattern
     * @return string
     */
    public function burnOrLike(string $column, string $pattern): string {
        return sprintf("OR %s LIKE '%s' ", $column, $pattern);
    }

    /**
     * Burn the SQL OR NOT LIKE clause.
     * @param string $column
     * @param string $pattern
     * @return string
     */
    public function burnOrNotLike(string $column, string $pattern): string {
        return sprintf("OR %s NOT LIKE '%s' ", $column, $pattern);
    }

    /**
     * Burn the SQL AND LIKE clause.
     * @param string $column
     * @param string $pattern
     * @return string
     */
    public function burnAndLike(string $column, string $pattern): string {
        return sprintf("AND %s LIKE '%s' ", $column, $pattern);
    }

    /**
     * Burn the SQL AND NOT LIKE clause.
     * @param string $column
     * @param string $pattern
     * @return string
     */
    public function burnAndNotLike(string $column, string $pattern): string {
        return sprintf("AND %s NOT LIKE '%s' ", $column, $pattern);
    }

    /**
     * Burn the SQL INNER JOIN clause.
     * @param string $name
     * @param string $condition
     * @param string $alias
     * @return string
     */
    public function burnInnerJoin(string $name, string $condition, string $alias=""): string {
        return sprintf("INNER JOIN %s %s ON %s ", $name, $alias, $condition);
    }

    /**
     * Burn the SQL LEFT JOIN clause.
     * @param string $name
     * @param string $condition
     * @param string $alias
     * @return string
     */
    public function burnLeftJoin(string $name, string $condition, string $alias=""): string {
        return sprintf("LEFT JOIN %s %s ON %s ", $name, $alias, $condition);
    }

    /**
     * Burn the SQL RIGHT JOIN clause.
     * @param string $name
     * @param string $condition
     * @param string $alias
     * @return string
     */
    public function burnRightJoin(string $name, string $condition, string $alias=""): string {
        return sprintf("RIGHT JOIN %s %s ON %s ", $name, $alias, $condition);
    }

    /**
     * Burn the SQL FULL OUTER JOIN clause.
     * @param string $name
     * @param string $condition
     * @param string $alias
     * @return string
     */
    public function burnFullJoin(string $name, string $condition, string $alias=""): string {
        return sprintf("FULL OUTER JOIN %s %s ON %s ", $name, $alias, $condition);
    }

    /**
     * Burn the SQL UNION clause.
     * @param string $sql
     * @return string
     */
    public function burnUnion(string $sql): string {
        return "UNION {$sql}";
    }

    /**
     * Burn the SQL UNION ALL clause.
     * @param string $sql
     * @return string
     */
    public function burnUnionAll(string $sql): string {
        return "UNION ALL {$sql}";
    }
}