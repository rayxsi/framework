<?php

namespace Artificers\Treaties\Database;

use Exception;

interface Result {
    /**
     * Fetches the next row from a result set as both.
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function fetchNextRow(): mixed;

    /**
     * Returns an array indexed by column name as returned in your result set.
     *
     * @return array
     *
     *@throws Exception
     */
    public function fetchNextAsAssoc(): array;

    /**
     * Returns an array indexed by column number starting at column 0.
     *
     * @return array
     *
     * @throws Exception
     */
    public function fetchNextAsNumeric(): array;

    /**
     * Returns an anonymous object with property names that correspond to the column names returned in your result set.
     *
     * @return Object
     *
     * @throws Exception
     */
    public function fetchNextAsObject(): Object;

    /**
     * Return next row as an anonymous object with column names as properties.
     *
     * @return Object
     */
    public function fetchNextAsLazy(): Object;

    /**
     * Returns a new instance of the requested class, mapping the columns of the result set to named properties in the class, and calling the constructor afterwards.
     *
     * @param string $class
     * @param array|null $constructorArgs
     * @return Object
     *
     * @throws Exception
     */
    public function fetchNextWithClass(string $class, ?array $constructorArgs = null): Object;

    /**
     * Returns a new instance of the requested class, call the constructor before mapping the columns of the result set to named properties in the class.
     *
     * @param string $class
     * @param array|null $constructorArgs
     * @return Object
     *
     * @throws Exception
     */
    public function fetchNextWithClassLateProps(string $class, ?array $constructorArgs = null): Object;

    /**
     *
     * @param object $class
     * @return Object
     *
     * @throws Exception
     */
    public function fetchNextWithUpdatingExistingClass(object $class): Object;

    /**
     * Returns an array with the same form as [fetchNextAsAssoc] method, except that if there are multiple columns with the same name, the value referred to by that key will be an array of all the values in the row that had that column name.
     *
     * @return array
     *
     * @throws Exception
     */
    public function fetchNextAsNamed(): array;

    /**
     *  Returns a single column from the next row of a result set.
     *
     * @param int $column
     * @return mixed
     *
     * @throws Exception
     */
    public function fetchSingleColumn(int $column = 0): mixed;

    /**
     * Fetches the next row and returns it as an object.
     * This method is alternative to [fetchNextAsObject] and [fetchNextWithClass].
     *
     * @param string|null $class
     * @param array $constructorArgs
     * @return object|false
     *
     * @throws Exception
     */
    public function fetchObject(?string $class = "stdClass", array $constructorArgs = []): object|false;

    /**
     * Fetches all rows from a result set as both.
     *
     * @return array|false
     *
     * @throws Exception
     */
    public function fetchAllRows(): array|false;

    /**
     * Fetches all rows from a result set as associative array.
     *
     * @return array|false
     *
     * @throws Exception
     */
    public function fetchAllRowsAsAssoc(): array|false;

    /**
     * Fetches all rows from a result set as associative array.
     *
     * @return array|false
     *
     * @throws Exception
     */
    public function fetchAllRowsAsObject(): array|false;

    /**
     * Returns an array containing all rows indexed by column number starting at column 0.
     *
     * @return array|false
     *
     * @throws Exception
     */
    public function fetchAllRowsAsNumeric(): array|false;

    /**
     * Returns the indicated 0-indexed column.
     *
     * @param int $column
     * @return array|false
     *
     * @throws Exception
     */
    public function fetchAllColumn(int $column = 0): array|false;

    /**
     * Returns array of new instance of the requested class, mapping the columns of the result set to named properties in the class, and calling the constructor afterwards.
     *
     * @param string $class
     * @param array|null $constructorArgs
     * @return array
     *
     * @throws Exception
     */
    public function fetchAllWithClass(string $class, ?array $constructorArgs): array;

    /**
     * Returns array of new instance of the requested class, call the constructor before mapping the columns of the result set to named properties in the class.
     *
     * @param string $class
     * @param array|null $constructorArgs
     * @return array
     *
     * @throws Exception
     */
    public function fetchAllWithClassLateProps(string $class, ?array $constructorArgs = null): array;

    /**
     * Returns the results of calling the specified function, using each row's columns as parameters in the call.
     *
     * @param callable $callback
     * @return array
     *
     * @throws Exception
     */
    public function fetchAllWithCallback(callable $callback): array;
}