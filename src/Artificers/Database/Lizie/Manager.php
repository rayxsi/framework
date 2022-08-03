<?php
declare(strict_types=1);

namespace Artificers\Database\Lizie;

use Artificers\Database\Lizie\Connections\Connection;
use Artificers\Database\Lizie\Exception\DriverRequiredException;
use Artificers\Database\Lizie\Exception\UnknownDriverException;

class Manager {
    private const DRIVER_MAPPER = [
        'mysql' => Driver\PDO\Mysql\Driver::class,
        'pgsql' => Driver\PDO\Pgsql\Driver::class
    ];

    /**
     * @throws DriverRequiredException
     * @throws UnknownDriverException
     */
    public static function make(array $params): Connection {
        //1. we have to make actual driver
        $driver = self::makeDriver($params);

        return new Connection($params, $driver);
    }

    /**
     * @param array $params
     * @return mixed
     * @throws UnknownDriverException
     * @throws DriverRequiredException
     */
    public static function makeDriver(array $params): mixed {
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
}