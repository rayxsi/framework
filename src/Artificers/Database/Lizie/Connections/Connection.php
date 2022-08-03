<?php
declare(strict_types=1);

namespace Artificers\Database\Lizie\Connections;

use Artificers\Database\Lizie\Driver\Exception;
use Artificers\Database\Lizie\Type;
use Artificers\Treaties\Database\Driver\Driver;

use Artificers\Treaties\Database\Driver\Exception as DriverException;
use Artificers\Treaties\Database\PDO\Connection as DriverConnection;


class Connection {
    protected Driver $_driver;

    protected ?DriverConnection $_connection;

    protected int $transactionLevel = 0;

    protected array $cachedSql = [];

    protected array $params = [];

    public function __construct(array $params, Driver $driver) {
        $this->params = $params;
        $this->_driver = $driver;
    }

    /**
     * @return bool
     */
    protected function _connect(): bool {
        if($this->_connection !== null) {
            return false;
        }

        $this->_connection = $this->_driver->connect($this->params);

        return true;
    }

    /**
     * @param string $sql
     * @return Statement
     * @throws DriverException
     */
    public function prepare(string $sql): Statement {
        $connection = $this->getConnection();
        try {
            $stmt = $connection->prepare($sql);
        }catch(DriverException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return new Statement($this, $stmt, $sql);
    }

    /**
    * @return DriverConnection
     */
    public function getConnection(): DriverConnection {
        $this->_connect();

        assert($this->_connection !== null);

        return $this->_connection;
    }

    protected function _close(): void {
        $this->_connection = null;
    }

    /**
     * @param string $sql
     * @param array $params
     * @param bool $forceToCache
     * @param string|null $cacheAlias
     * @return Result
     * @throws Exception
     */
    public function runQuery(string $sql, array $params = [], bool $forceToCache = false, ?string $cacheAlias = null): Result {
        $connection = $this->getConnection();

        if($forceToCache) {
            $this->cachedSql[$cacheAlias] = $sql;
        }

        try {
            if(!empty($params)) {
                $stmt = $connection->prepare($sql);
                $result = $stmt->execute($params);
            }else {
                $result = $connection->query($sql);
            }
        }catch(DriverException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return new Result($result, $this);
    }

    /**
     * @throws Exception
     */
    public function runCachedQuery(string $cacheAlias, array $params = []): Result {
        if(isset($this->cachedSql[$cacheAlias])) {
            $sql = $this->cachedSql[$cacheAlias];

            $connection = $this->getConnection();

            try {
                if(!empty($params)) {
                    $stmt = $connection->prepare($sql);
                    $result = $stmt->execute($params);
                }else {
                    $result = $connection->query($sql);
                }
            }catch(DriverException $e) {
                throw new Exception($e->getMessage(), $e->getCode());
            }

            return new Result($result, $this);
        }

        throw new Exception('Undefined cache alias.');
    }

    public function quote(string $string, int $type = Type::PARAM_STR): string|false {
        $connection = $this->getConnection();

        return $connection->quote($string, $type);
    }

    /**
     * @throws Exception
     */
    public function startTransaction(): bool {
        $connection = $this->getConnection();

        if($this->transactionLevel === 0) {
            try {
               return $connection->startTransaction();
            }catch(DriverException $e) {
                throw new Exception($e->getMessage(), $e->getCode());
            }
        }else {
            try {
                $connection->exec("SAVEPOINT {$this->_generateSavePointMarker()}");
            }catch(DriverException $e) {
                throw new Exception($e->getMessage(), $e->getCode());
            }
        }

        $this->transactionLevel++;

        return true;
    }

    /**
     * @throws Exception
     */
    public function commit(): bool {
        $this->transactionLevel--;
        $connection = $this->getConnection();
        $result = false;

        if($this->transactionLevel === 0) {
            try {
                $result = $connection->commit();
            }catch(DriverException $e) {
                throw new Exception($e->getMessage(), $e->getCode());
            }
        }else {
            try {
                $connection->exec("RELEASE SAVEPOINT {$this->_generateSavePointMarker()}");
            }catch(DriverException $e) {
                throw new Exception($e->getMessage(), $e->getCode());
            }
        }

        return $result;
    }

    /**
     * @throws Exception
     */
    public function rollback(): bool {
        $this->transactionLevel--;
        $result = false;
        $connection = $this->getConnection();

        if($this->transactionLevel === 0) {
            try {
                $result = $connection->rollback();
            }catch(DriverException $e) {
                throw new Exception($e->getMessage(), $e->getCode());
            }
        }else {
            try {
                $connection->exec("ROLLBACK TO SAVEPOINT {$this->_generateSavePointMarker()}");
            } catch (DriverException $e) {
                throw new Exception($e->getMessage(), $e->getCode());
            }
        }

        return $result;
    }

    protected function _generateSavePointMarker(): string {
        return "LIZIE_POINTER_{$this->transactionLevel}";
    }

    /**
     * @throws Exception
     */
    public function lastInsertedId(?string $name = null): string|false {
        try {
            return $this->getConnection()->lastInsertedId($name);
        }catch(DriverException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public function inTransaction(): bool {
        return $this->getConnection()->inTransaction();
    }
}