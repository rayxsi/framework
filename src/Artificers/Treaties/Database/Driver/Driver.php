<?php

namespace Artificers\Treaties\Database\Driver;

use Artificers\Treaties\Database\PDO\Connection as DriverConnection;

interface Driver {
    /**
     * @param array $params
     * @return DriverConnection
     */
    public function connect(array $params): DriverConnection;

    /**
     * @param array $params
     * @return string
     */
    public function makeDsn(array $params): string;
}