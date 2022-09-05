<?php

namespace Artificers\Database\Raintter;

use Artificers\Database\Lizie\Connections\Connection;
use Artificers\Foundation\Rayxsi;

class MDBConjunction {
    private Connection $connection;

    public function __construct(Rayxsi $rXsiApp) {
      $this->connection = $rXsiApp['db']->connection();
    }

    public function set(Model &$model): void {
        $model->FirstName = "woo";
    }

    public function transport(Model $model): bool {


        return false;
    }
}