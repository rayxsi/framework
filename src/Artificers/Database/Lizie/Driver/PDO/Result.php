<?php

namespace Artificers\Database\Lizie\Driver\PDO;

use Artificers\Treaties\Database\PDO\Result as ResultTreaties;
use Artificers\Treaties\Database\Driver\Exception;
use Artificers\Database\Lizie\Driver\Exception as DriverException;

use PDO;
use PDOException;
use PDOStatement;

final class Result implements ResultTreaties {

    private PDOStatement $stmt;
    private bool $executionStatus;

    public function __construct(PDOStatement $stmt, bool $executionStatus) {
        $this->stmt = $stmt;
        $this->executionStatus = $executionStatus;
    }

    public function getExecStatus(): bool {
        return $this->executionStatus;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function fetchNextRow(): mixed {
        return $this->fetch(PDO::FETCH_BOTH);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function fetchNextAsAssoc(): array {
        return $this->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function fetchNextAsNumeric(): array {
        return $this->fetch(PDO::FETCH_NUM);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function fetchNextAsObject(): object {
        return $this->fetch(PDO::FETCH_OBJ);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function fetchNextAsLazy(): object {
       return $this->fetch(PDO::FETCH_LAZY);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function fetchNextWithClass(string $class, ?array $constructorArgs = null): object {
        $this->stmt->setFetchMode(PDO::FETCH_CLASS, $class, $constructorArgs);

        return $this->fetch();
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function fetchNextWithClassLateProps(string $class, ?array $constructorArgs = null): object {
        $this->stmt->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, $class, $constructorArgs);

        return $this->fetch();
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function fetchNextWithUpdatingExistingClass(object $class): object {
        $this->stmt->setFetchMode(PDO::FETCH_INTO, $class);

        return $this->fetch();
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function fetchNextAsNamed(): array {
       return $this->fetch(PDO::FETCH_NAMED);
    }

    /**
     * @inheritDoc
     */
    public function fetchSingleColumn(int $column = 0): mixed {
       return $this->stmt->fetchColumn($column);
    }

    /**
     * @inheritDoc
     */
    public function fetchObject(?string $class = "stdClass", array $constructorArgs = []): object|false {
        return $this->stmt->fetchObject($class, $constructorArgs);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function fetchAllRows(): array|false {
        return $this->fetchAll(PDO::FETCH_BOTH);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function fetchAllColumn(int $column = 0): array|false {
        return $this->fetchAll(PDO::FETCH_COLUMN, $column);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function fetchAllRowsAsAssoc(): array|false {
        return $this->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function fetchAllRowsAsObject(): array|false {
        return $this->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function fetchAllRowsAsNumeric(): array|false {
        return $this->fetchAll(PDO::FETCH_NUM);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function fetchAllWithClass(string $class, ?array $constructorArgs): array {
        return $this->fetchAll(PDO::FETCH_CLASS, $class, $constructorArgs);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function fetchAllWithClassLateProps(string $class, ?array $constructorArgs = null): array {
        return $this->fetchAll(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, $class, $constructorArgs);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function fetchAllWithCallback(callable $callback): array {
       return $this->fetchAll(PDO::FETCH_FUNC, $callback);
    }

    /**
     * @param int $mode
     * @return mixed
     * @throws Exception
     */
    private function fetch(int $mode = PDO::FETCH_DEFAULT): mixed {
        try {
            return $this->stmt->fetch($mode);
        }catch(PDOException $e) {
            throw new DriverException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param int $mode
     * @return array|bool
     * @throws Exception
     */
    private function fetchAll(int $mode): array|bool {
        try {
            return $this->stmt->fetchAll($mode);
        }catch(PDOException $e) {
            throw new DriverException($e->getMessage(), $e->getCode());
        }
    }
}