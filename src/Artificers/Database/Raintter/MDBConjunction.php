<?php

namespace Artificers\Database\Raintter;

use Artificers\Database\Lizie\Connections\Connection;
use Artificers\Database\Lizie\Query\Builder;
use Artificers\Foundation\Rayxsi;

class MDBConjunction {
    private Connection $connection;

    public function __construct(Rayxsi $rXsiApp) {
      $this->connection = $rXsiApp['db']->connection();
    }

    public function transport(Model $model): bool {


        return false;
    }

    public function getQueryBuilder(): Builder {
        return new Builder($this->connection);
    }
}