<?php
declare(strict_types=1);

namespace Artificers\Database\Lizie\Driver\PDO;

use Artificers\Database\Lizie\Driver\Exception as DriverException;
use Artificers\Database\Lizie\Type;
use Artificers\Treaties\Database\Driver\Exception;
use Artificers\Treaties\Database\PDO\Connection as ConnectionTreaties;
use Artificers\Treaties\Database\PDO\Result as ResultTreaties;
use Artificers\Treaties\Database\PDO\Statement as StatementTreaties;

use PDO;
use PDOException;
use PDOStatement;

final class Connection implements ConnectionTreaties {

    private PDO $connection;

    public function __construct(PDO $connection) {
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connection = $connection;
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function prepare(string $sql): StatementTreaties {
        try {
            $stmt = $this->connection->prepare($sql);
            assert($stmt instanceof PDOStatement);

            return new Statement($stmt);
        }catch(PDOException $e) {
            throw new DriverException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function exec(string $sql): int {
        try {
            $row = $this->connection->exec($sql);
            assert($row !== false);
            return $row;
        }catch(PDOException $e) {
            throw new DriverException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function query(string $sql): ResultTreaties {
        try {
            $stmt = $this->connection->query($sql);
            assert($stmt instanceof PDOStatement);

            return new Result($stmt);
        }catch(PDOException $e) {
            throw new DriverException($e->getMessage(), $e->getCode());
        }
    }

    /**
     *@inheritDoc
     */
    public function quote(string $string, $type=Type::PARAM_STR): string|false {
        return $this->connection->quote($string, $type);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function startTransaction(): bool {
        try {
            return $this->connection->beginTransaction();
        }catch(PDOException $e) {
            throw new DriverException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function commit(): bool {
        try {
            return $this->connection->commit();
        }catch(PDOException $e) {
            throw new DriverException($e->getMessage(), $e->getCode());
        }

    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function rollback(): bool {
        try {
            return $this->connection->rollBack();
        }catch(PDOException $e) {
            throw new DriverException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     * @throws DriverException
     */
    public function lastInsertedId(?string $name = null): string|false {

        try {
            if(is_null($name)) {
                return $this->connection->lastInsertId();
            }

            return $this->connection->lastInsertId($name);
        }catch(PDOException $e) {
            throw new DriverException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     */
    public function inTransaction(): bool {
        return $this->connection->inTransaction();
    }
}