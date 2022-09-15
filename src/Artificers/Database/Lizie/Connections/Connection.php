<?php
declare(strict_types=1);

namespace Artificers\Database\Lizie\Connections;

use Artificers\Database\Lizie\Exception\LizieException;
use Artificers\Database\Lizie\Query\Builder as QueryBuilder;
use Artificers\Database\Lizie\Schema\Grammars\Grammar;
use Artificers\Database\Lizie\Query\Grammars\Grammar as QueryGrammar;
use Artificers\Database\Lizie\Schema\Schema as SchemaBuilder;
use Artificers\Database\Lizie\Type;
use Artificers\Treaties\Database\Driver\Driver;

use Artificers\Treaties\Database\Driver\Exception as DriverException;
use Artificers\Treaties\Database\PDO\Connection as DriverConnection;

class Connection {
    protected Driver $_driver;

    protected string $schema;

    protected ?DriverConnection $_connection = null;

    protected int $transactionLevel = 0;

    protected array $cachedSql = [];

    protected array $params = [];

    protected Grammar $schemaGrammar;
    protected QueryGrammar $queryGrammar;

    public function __construct(array $params, Driver $driver) {
        $this->params = $params;
        $this->_driver = $driver;
    }

    /**
     * Generate a driver connection.
     *
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
     * Prepare a sql command.
     *
     * @param string $sql
     * @return Statement
     * @throws DriverException
     */
    public function prepare(string $sql): Statement {
        $connection = $this->getConnection();
        try {
            $stmt = $connection->prepare($sql);
        }catch(DriverException $e) {
            throw new LizieException($e->getMessage(), $e->getCode());
        }

        return new Statement($this, $stmt, $sql);
    }

    /**
     * Generate the connection and return it.
     *
    * @return DriverConnection
     */
    protected function getConnection(): DriverConnection {
        $this->_connect();

        assert($this->_connection !== null);

        return $this->_connection;
    }

    protected function _close(): void {
        $this->_connection = null;
    }

    /**
     * Prepare the sql command, bind the value, execute the sql statement and return the result object.
     *
     * @param string $sql
     * @param array $params
     * @param bool $forceToCache
     * @param string|null $cacheAlias
     * @return Result
     * @throws DriverException
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
            throw new LizieException($e->getMessage(), $e->getCode());
        }

        return new Result($result, $this);
    }

    /**
     * Prepare the cached sql command, bind the value, execute the sql statement and return the result object.
     *
     * @param string $cacheAlias
     * @param array $params
     * @return Result
     * @throws DriverException
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
                throw new LizieException($e->getMessage(), $e->getCode());
            }

            return new Result($result, $this);
        }

        throw new LizieException('Undefined cache alias.');
    }

    /**
     * Quotes a string for use in a query.
     *
     * @param string $string
     * @param int $type
     * @return string|false
     */
    public function quote(string $string, int $type = Type::PARAM_STR): string|false {
        $connection = $this->getConnection();

        return $connection->quote($string, $type);
    }

    /**
     * Start the database transaction.
     *
     * @throws DriverException
     */
    public function startTransaction(): bool {
        $connection = $this->getConnection();

        if($this->transactionLevel === 0) {
            try {
               return $connection->startTransaction();
            }catch(DriverException $e) {
                throw new LizieException($e->getMessage(), $e->getCode());
            }
        }else {
            try {
                $connection->exec("SAVEPOINT {$this->_generateSavePointMarker()}");
            }catch(DriverException $e) {
                throw new LizieException($e->getMessage(), $e->getCode());
            }
        }

        $this->transactionLevel++;

        return true;
    }

    /**
     * Save the transaction.
     *
     * @throws DriverException
     */
    public function commit(): bool {
        $this->transactionLevel--;
        $connection = $this->getConnection();
        $result = false;

        if($this->transactionLevel === 0) {
            try {
                $result = $connection->commit();
            }catch(DriverException $e) {
                throw new LizieException($e->getMessage(), $e->getCode());
            }
        }else {
            try {
                $connection->exec("RELEASE SAVEPOINT {$this->_generateSavePointMarker()}");
            }catch(DriverException $e) {
                throw new LizieException($e->getMessage(), $e->getCode());
            }
        }

        return $result;
    }

    /**
     * Revert the transaction.
     *
     * @throws DriverException
     */
    public function rollback(): bool {
        $this->transactionLevel--;
        $result = false;
        $connection = $this->getConnection();

        if($this->transactionLevel === 0) {
            try {
                $result = $connection->rollback();
            }catch(DriverException $e) {
                throw new LizieException($e->getMessage(), $e->getCode());
            }
        }else {
            try {
                $connection->exec("ROLLBACK TO SAVEPOINT {$this->_generateSavePointMarker()}");
            } catch (DriverException $e) {
                throw new LizieException($e->getMessage(), $e->getCode());
            }
        }

        return $result;
    }

    /**
     * Generate save point marker.
     *
     * @return string
     */
    protected function _generateSavePointMarker(): string {
        return "LIZIE_POINTER_{$this->transactionLevel}";
    }

    /**
     * Get the id of last inserted record.
     *
     * @param string|null $name
     * @return string|false
     * @throws DriverException
     */
    public function lastInsertedId(?string $name = null): string|false {
        try {
            return $this->getConnection()->lastInsertedId($name);
        }catch(DriverException $e) {
            throw new LizieException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Check if in transaction or not.
     *
     * @return bool
     */
    public function inTransaction(): bool {
        return $this->getConnection()->inTransaction();
    }

    /**
     * Return the schema object.
     *
     * @return SchemaBuilder
     */
    public function getSchemaBuilder(): SchemaBuilder {
        return new SchemaBuilder($this);
    }

    /**
     * Set schema grammar.
     *
     * @param Grammar $grammar
     * @return $this
     */
    public function setSchemaGrammar(Grammar $grammar): static {
        $this->schemaGrammar = $grammar;

        return $this;
    }

    /**
     * Set query grammar.
     *
     * @param QueryGrammar $grammar
     * @return $this
     */
    public function setQueryGrammar(QueryGrammar $grammar):static {
        $this->queryGrammar = $grammar;

        return $this;
    }

    /**
     * Get schema grammar.
     *
     * @return Grammar
     */
    public function getSchemaGrammar(): Grammar {
        return $this->schemaGrammar;
    }

    /**
     * Get query grammar.
     *
     * @return QueryGrammar
     */
    public function getQueryGrammar(): QueryGrammar {
        return $this->queryGrammar;
    }

    public function getQueryBuilder(): QueryBuilder {

        return new QueryBuilder($this);
    }

    /**
     * Return the database schema name.
     *
     * @return string
     */
    public function getSchema(): string {
        return $this->schema;
    }

    /**
     * Set the database schema name.
     *
     * @param string $schema
     * @return void
     */
    public function setSchema(string $schema): void {
        $this->schema = $schema;
    }
}