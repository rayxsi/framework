<?php
declare(strict_types=1);

namespace Artificers\Database\Lizie\Driver\PDO\Mysql;

use Artificers\Database\Lizie\Driver\PDO\Connection;
use Artificers\Treaties\Database\Driver\Driver as DriverTreaties;
use Artificers\Treaties\Database\PDO\Connection as DriverConnection;
use Exception;
use PDO;
use PDOException;

final class Driver implements DriverTreaties {

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function connect(array $params): DriverConnection {
        $dsn = $this->makeDsn($params);

        $driverOption = $params['options'] ?? [];

        if(!empty($driverOption['persistent_connection'])) {
            $driverOption[PDO::ATTR_PERSISTENT] = $driverOption['persistent_connection'];
        }

        try{
            $pdo = new PDO($dsn, $params['user'], $params['password'], $driverOption);
        }catch(PDOException $e) {
            throw new Exception("Connection failed. [{$e->getMessage()}]", $e->getCode());
        }

        return new Connection($pdo);
    }

    /**
     * @inheritDoc
     */
    public function makeDsn(array $params): string {
        $dsn = "mysql:";

        if (isset($params['host']) && $params['host'] !== '') {
            $dsn .= "host={$params['host']};";
        }

        if (isset($params['port'])) {
            $dsn .= "port={$params['port']};";
        }

        if (isset($params['name'])) {
            $dsn .= "dbname={$params['name']};";
        }

        if (isset($params['unix_socket'])) {
            $dsn .= "unix_socket={$params['unix_socket']};";
        }

        if (isset($params['charset'])) {
            $dsn .= "charset={$params['charset']};";
        }

        return $dsn;
    }
}