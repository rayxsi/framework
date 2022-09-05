<?php

namespace Artificers\Database;

use Artificers\Supports\ServiceRegister;

class DatabaseServiceRegister extends ServiceRegister {
    public function register(): void {
        $this->registerDBServices();
    }

    private function registerDBServices(): void {
        $this->registerDB();

        $this->rXsiApp->bind("db.connection", function($rXsiApp) {
            return $rXsiApp['db']->connection();
        });

        $this->rXsiApp->bind('db.schema', function($rXsiApp) {
            return $rXsiApp['db']->connection()->getSchemaBuilder();
        });

        $this->rXsiApp->bind('db.builder', function($rXsiApp) {
            return $rXsiApp['db']->connection()->getQueryBuilder();
        });
    }

    private function registerDB(): void {
        $this->rXsiApp->singleton('db', function($rXsiApp) {
            $repo = $rXsiApp['config'];
            $driver = $repo->get('database.default');

            $connectionParams = $repo->get("database.connections.{$driver}");

            return new DatabaseManager($rXsiApp, $connectionParams);
        });
    }
}