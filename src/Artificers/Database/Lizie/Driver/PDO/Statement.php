<?php
declare(strict_types=1);

namespace Artificers\Database\Lizie\Driver\PDO;

use Artificers\Database\Lizie\Type;
use Artificers\Treaties\Database\Driver\Exception;
use Artificers\Database\Lizie\Driver\Exception as DriverException;
use Artificers\Treaties\Database\PDO\Statement as StatementTreaties;
use Artificers\Treaties\Database\PDO\Result as ResultTreaties;
use PDOException;
use PDOStatement;

final class Statement implements StatementTreaties{
    private PDOStatement $stmt;

    public function __construct(PDOStatement $stmt) {
        $this->stmt = $stmt;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function bindValue(int|string $param, mixed $value, int $type = Type::PARAM_STR): bool {
        try {
            return $this->stmt->bindValue($param, $value, $type);
        }catch(PDOException $e) {
            throw new DriverException($e->getMessage(), (int)$e->getCode());
        }
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function bindParam(int|string $param, mixed &$var, int $type = Type::PARAM_STR, int $maxLength = 0, mixed $driverOptions = null): bool {
        try {
            return $this->stmt->bindParam($param, $var, $type, $maxLength, $driverOptions);
        }catch(PDOException $e) {
            throw new DriverException($e->getMessage(), (int)$e->getCode());
        }
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function bindColumn(int|string $column, mixed &$var, int $type = Type::PARAM_STR, int $maxLength = 0, mixed $driverOptions = null): bool {
        try {
            return $this->stmt->bindColumn($column, $var, $type, $maxLength, $driverOptions);
        }catch(PDOException $e) {
            throw new DriverException($e->getMessage(), (int)$e->getCode());
        }
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function execute(?array $params = null): ResultTreaties {
        try {
            $this->stmt->execute($params);
        }catch(PDOException $e) {
            throw new DriverException($e->getMessage(), (int)$e->getCode());
        }

        return new Result($this->stmt);
    }
}