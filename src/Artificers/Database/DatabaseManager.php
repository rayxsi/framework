<?php

namespace Artificers\Database;

use Artificers\Database\Lizie\Connections\Connection;
use Artificers\Database\Lizie\Exception\DriverRequiredException;
use Artificers\Database\Lizie\Exception\UnknownDriverException;
use Artificers\Database\Lizie\Manager;
use Artificers\Foundation\Rayxsi;
use Exception;

class DatabaseManager {
    private Rayxsi $rXsiApp;
    private array $params = [];

    private ?Connection $connection = null;

    public function __construct(Rayxsi $container, array $params) {
        $this->rXsiApp = $container;
        $this->params = $params;
    }

    /**
     * @throws Exception
     */
    public function connection(): Connection {
        try {
            if(is_null($this->connection)) {
                $this->connection = Manager::make($this->params);
            }
        }catch(DriverRequiredException|UnknownDriverException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return $this->connection;
    }

    /**
     * @throws Exception
     */
    public function close(): bool {
        if(!is_null($this->connection)) {
            $this->connection = null;

            return true;
        }

        throw new Exception("There is no active database connection.");
    }
}