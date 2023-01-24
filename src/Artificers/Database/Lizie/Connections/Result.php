<?php
declare(strict_types=1);

namespace Artificers\Database\Lizie\Connections;

use Artificers\Database\Lizie\Exception\LizieException;
use Artificers\Treaties\Database\PDO\Result as DriverResult;
use Artificers\Treaties\Database\Driver\Exception as DriverException;
use Artificers\Treaties\Database\Result as ResultTreaties;


class Result implements ResultTreaties{
    private DriverResult $result;
    private Connection $connection;

    public function __construct(DriverResult $result, Connection $connection) {
        $this->result = $result;
        $this->connection = $connection;
    }

    public function getExecStatus(): bool {
        return $this->result->getExecStatus();
    }

    /**
     * Fetches the next row from a result set as both.
     *
     * @return array
     * @throws LizieException
     */
    public function fetchNextRow(): array {
        try {
            return $this->result->fetchNextRow();
        }catch(DriverException $e) {
            throw new LizieException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     * @throws LizieException
     */
    public function fetchNextAsAssoc(): array {
        try {
            return $this->result->fetchNextAsAssoc();
        }catch(DriverException $e) {
            throw new LizieException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     * @throws LizieException
     */
    public function fetchNextAsNumeric(): array {
        try {
            return $this->result->fetchNextAsNumeric();
        }catch(DriverException $e) {
            throw new LizieException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     * @throws LizieException
     */
    public function fetchNextAsObject(): object {
        try {
            return $this->result->fetchNextAsObject();
        }catch(DriverException $e) {
            throw new LizieException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     * @throws LizieException
     */
    public function fetchNextAsLazy(): object {
        try {
            return $this->result->fetchNextAsLazy();
        }catch(DriverException $e) {
            throw new LizieException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     * @throws LizieException
     */
    public function fetchNextWithClass(string $class, ?array $constructorArgs = null): object {
        try {
            return $this->result->fetchNextWithClass($class, $constructorArgs);
        }catch(DriverException $e) {
            throw new LizieException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     * @throws LizieException
     */
    public function fetchNextWithClassLateProps(string $class, ?array $constructorArgs = null): object {
        try {
            return $this->result->fetchNextWithClassLateProps($class, $constructorArgs);
        }catch(DriverException $e) {
            throw new LizieException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     * @throws LizieException
     */
    public function fetchNextWithUpdatingExistingClass(object $class): object {
        try {
            return $this->result->fetchNextWithUpdatingExistingClass($class);
        }catch(DriverException $e) {
            throw new LizieException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     * @throws LizieException
     */
    public function fetchNextAsNamed(): array {
        try {
            return $this->result->fetchNextAsNamed();
        }catch(DriverException $e) {
            throw new LizieException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     * @throws LizieException
     */
    public function fetchSingleColumn(int $column = 0): mixed {
        try {
            return $this->result->fetchSingleColumn($column);
        }catch(DriverException $e) {
            throw new LizieException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     * @throws LizieException
     */
    public function fetchObject(?string $class = "stdClass", array $constructorArgs = []): object|false {
        try {
            return $this->result->fetchObject($class, $constructorArgs);
        }catch(DriverException $e) {
            throw new LizieException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     * @throws LizieException
     */
    public function fetchAllRows(): array|false {
        try {
            return $this->result->fetchAllRows();
        }catch(DriverException $e) {
            throw new LizieException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     * @throws LizieException
     */
    public function fetchAllRowsAsAssoc(): array|false {
        try {
            return $this->result->fetchAllRowsAsAssoc();
        }catch(DriverException $e) {
            throw new LizieException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     * @throws LizieException
     */
    public function fetchAllRowsAsObject(): array|false {
        try {
            return $this->result->fetchAllRowsAsObject();
        }catch(DriverException $e) {
            throw new LizieException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     * @throws LizieException
     */
    public function fetchAllRowsAsNumeric(): array|false {
        try {
            return $this->result->fetchAllRowsAsNumeric();
        }catch(DriverException $e) {
            throw new LizieException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     * @throws LizieException
     */
    public function fetchAllColumn(int $column = 0): array|false {
        try {
            return $this->result->fetchAllColumn($column);
        }catch(DriverException $e) {
            throw new LizieException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     * @throws LizieException
     */
    public function fetchAllWithClass(string $class, ?array $constructorArgs): array {
        try {
            return $this->result->fetchAllWithClass($class, $constructorArgs);
        }catch(DriverException $e) {
            throw new LizieException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     * @throws LizieException
     */
    public function fetchAllWithClassLateProps(string $class, ?array $constructorArgs = null): array {
        try {
            return $this->result->fetchAllWithClassLateProps($class, $constructorArgs);
        }catch(DriverException $e) {
            throw new LizieException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     * @throws LizieException
     */
    public function fetchAllWithCallback(callable $callback): array {
        try {
            return $this->result->fetchAllWithCallback($callback);
        }catch(DriverException $e) {
            throw new LizieException($e->getMessage(), $e->getCode());
        }
    }
}