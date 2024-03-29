<?php
declare(strict_types=1);

namespace Artificers\Database\Lizie;

use Artificers\Database\Lizie\Connections\Connection;
use Artificers\Database\Lizie\Exception\DriverRequiredException;
use Artificers\Database\Lizie\Exception\UnknownDriverException;

class Manager {
    /**
    * Maps all the driver with key.
     * @const DRIVER_MAPPER
     */
    private const DRIVER_MAPPER = [
        'mysql' => Driver\PDO\Mysql\Driver::class,
        'pgsql' => Driver\PDO\Pgsql\Driver::class
    ];

    /**
     * Maps all the schema grammar with key.
     * @const DRIVER_MAPPER
     */
    private const SCHEMA_GRAMMAR_MAPPER = [
        'mysql' => Schema\Grammars\MysqlGrammar::class,
        'pgsql' => Schema\Grammars\PgsqlGrammar::class
    ];

    /**
     * Maps all the query grammar with key.
     * @const DRIVER_MAPPER
     */
    private const QUERY_GRAMMAR_MAPPER = [
        'mysql' => Query\Grammars\MysqlGrammar::class,
        'pgsql' => Query\Grammars\MysqlGrammar::class
    ];

    /**
     * Generate database connection and return it.
     * @param array $params
     * @return Connection
     * @throws DriverRequiredException
     * @throws UnknownDriverException
     */
    public static function make(array $params): Connection {
        //1. we have to make actual driver
        $driver = self::makeDriver($params);
        $connection = new Connection($params, $driver);
        $connection->setSchemaGrammar(self::makeSchemaGrammar($params))->setQueryGrammar(self::makeQueryGrammar($params))->setSchema($params['name']);

        return $connection;
    }

    /**
     * Create appropriate driver instance.
     * @param array $params
     * @return mixed
     * @throws UnknownDriverException
     * @throws DriverRequiredException
     */
    private static function makeDriver(array $params): mixed {
        //We have to check in params for driver.
        if(isset($params['driver'])) {
            if(!isset(self::DRIVER_MAPPER[$params['driver']])) {
                throw new UnknownDriverException("Unknown driver is set to the connection params");
            }

            $driverClass = self::DRIVER_MAPPER[$params['driver']];

            return new $driverClass();
        }

        throw new DriverRequiredException('Driver is required :(');
    }

    /**
     * Create schema grammar.
     * @param array $params
     * @return mixed
     * @throws DriverRequiredException
     * @throws UnknownDriverException
     */
    private static function makeSchemaGrammar(array $params): mixed {
        if(isset($params['driver'])) {
            if(!isset(self::DRIVER_MAPPER[$params['driver']])) {
                throw new UnknownDriverException("Unknown driver is set to the connection params");
            }

            $schemaGrammarClass = self::SCHEMA_GRAMMAR_MAPPER[$params['driver']];

            return new $schemaGrammarClass();
        }

        throw new DriverRequiredException('Driver is required :(');
    }

    /**
     * Return the Schema instance.
     *
     * @param Connection $connection
     * @return Schema\Schema
     */
    public static function schema(Connection $connection): Schema\Schema {
        return new Schema\Schema($connection);
    }

    /**
     * @throws DriverRequiredException
     * @throws UnknownDriverException
     */
    private static function makeQueryGrammar(array $params): mixed {
        if(isset($params['driver'])) {
            if(!isset(self::DRIVER_MAPPER[$params['driver']])) {
                throw new UnknownDriverException("Unknown driver is set to the connection params");
            }

            $queryGrammar = self::QUERY_GRAMMAR_MAPPER[$params['driver']];

            return new $queryGrammar();
        }

        throw new DriverRequiredException('Driver is required :(');
    }
}