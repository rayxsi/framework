<?php

namespace Artificers\Database\Lizie\Driver\PDO\Pgsql;

use Artificers\Treaties\Database\Driver\Driver as DriverTreaties;
use Artificers\Treaties\Database\PDO\Connection;

final class Driver implements DriverTreaties{

    /**
     * @inheritDoc
     */
    public function connect(array $params): Connection
    {
        // TODO: Implement connect() method.
    }

    /**
     * @inheritDoc
     */
    public function makeDsn(array $params): string
    {
        // TODO: Implement makeDsn() method.
    }
}