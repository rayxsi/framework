<?php

namespace Artificers\Treaties\Database\PDO;

use Artificers\Database\Lizie\Type;
use Artificers\Treaties\Database\Driver\Exception;

interface Statement {
    /**
     * Binds a value to a parameter.
     *
     * @param string|int $param
     * @param mixed $value
     * @param int $type
     * @return bool
     * @throws Exception
     */
    public function bindValue(string|int $param, mixed $value, int $type = Type::PARAM_STR): bool;

    /**
     * Binds a parameter to the specified variable name.
     *
     * @param string|int $param
     * @param mixed $var
     * @param int $type
     * @param int $maxLength
     * @param mixed|null $driverOptions
     * @return bool
     * @throws Exception
     */
    public function bindParam( string|int $param, mixed &$var, int $type = Type::PARAM_STR, int $maxLength = 0, mixed $driverOptions = null): bool;

    /**
     * Bind a column to a PHP variable.
     *
     * @param string|int $column
     * @param mixed $var
     * @param int $type
     * @param int $maxLength
     * @param mixed|null $driverOptions
     * @return bool
     * @throws Exception
     */
    public function bindColumn(string|int $column, mixed &$var, int $type = Type::PARAM_STR, int $maxLength = 0, mixed $driverOptions = null): bool;

    /**
     *  Executes a prepared statement.
     *
     * @param array|null $params
     * @return Result
     * @throws Exception
     */
    public function execute(?array $params = null): Result;
}