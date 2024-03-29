<?php
declare(strict_types=1);

namespace Artificers\Database\Lizie\Connections;

use Artificers\Database\Lizie\Exception\LizieException;
use Artificers\Database\Lizie\Type;
use Artificers\Treaties\Database\Driver\Exception as DriverException;
use Artificers\Treaties\Database\PDO\Statement as DriverStatement;

class Statement {
    protected Connection $_conn;

    protected DriverStatement $_stmt;

    protected string $_sql;

    private array $params = [];
    private array $columns = [];
    private array $types = [];

    public function __construct(Connection $connection, DriverStatement $stmt, string $sql) {
        $this->_conn = $connection;
        $this->_stmt = $stmt;
        $this->_sql = $sql;
    }

    /**
     * Binds a value to a parameter.
     * @param int|string $param
     * @param mixed $value
     * @param int $type
     * @return bool
     * @throws DriverException
     */
    public function bindValue(int|string $param, mixed $value, int $type=Type::PARAM_STR): bool {
        $this->params[$param] = $value;
        $this->types[$param] = $type;

        try {
            return $this->_stmt->bindValue($param, $value, $type);
        }catch(DriverException $e) {
            throw new LizieException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Binds a parameter to the specified variable name.
     * @param int|string $param
     * @param mixed $var
     * @param int $type
     * @param int $maxLength
     * @param mixed|null $driverOptions
     * @return bool
     * @throws DriverException
     */
    public function bindParam(int|string $param, mixed &$var, int $type = Type::PARAM_STR, int $maxLength = 0, mixed $driverOptions = null): bool {
        $this->params[$param] = $var;
        $this->types[$param] = $type;

        try {
            if(func_num_args() > 3) {
                return $this->_stmt->bindParam($param, $var, $type, $maxLength, $driverOptions);
            }
            return $this->_stmt->bindParam($param, $var, $type);
        }catch(DriverException $e) {
            throw new LizieException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Bind a column to a PHP variable.
     * @param string|int $column
     * @param mixed $var
     * @param int $type
     * @param int $maxLength
     * @param mixed|null $driverOptions
     * @return bool
     * @throws DriverException
     */
    public function bindColumn(string|int $column, mixed &$var, int $type = Type::PARAM_STR, int $maxLength = 0, mixed $driverOptions = null): bool {
        $this->columns[$column] = $var;
        $this->types[$column] = $var;

        try {
            if(func_num_args() > 3) {
                return $this->_stmt->bindColumn($column, $var, $type, $maxLength, $driverOptions);
            }
            return $this->_stmt->bindParam($column, $var, $type);
        }catch(DriverException $e) {
            throw new LizieException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Executes a prepared statement.
     * @param mixed $params
     * @return Result
     * @throws DriverException
     */
    public function run(mixed $params = null): Result {
        try{
            return new Result($this->_stmt->execute($params), $this->_conn);
        }catch(DriverException $e) {
            throw new LizieException($e->getMessage(), $e->getCode());
        }
    }
}