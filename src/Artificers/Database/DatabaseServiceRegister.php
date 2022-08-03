<?php

namespace Artificers\Database;

use Artificers\Supports\ServiceRegister;

class DatabaseServiceRegister extends ServiceRegister {
    public function register(): void {
        $this->rXsiApp->singleton('db', function($rXsiApp) {
            $repo = $rXsiApp['config'];
            $driver = $repo->get('database.default');

            $connectionParams = $repo->get("database.connections.{$driver}");

            return new DatabaseManager($rXsiApp, $connectionParams);
        });
    }
}