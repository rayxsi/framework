<?php

namespace Artificers\Network\Routing;

use Artificers\Container\Container;
use Artificers\Supports\ServiceRegister;

class RouteServiceRegister extends ServiceRegister {
    public function register(): void {
        $this->registerRouter();
    }

    private function registerRouter() {
        $this->rXsiApp->singleton('router', function(Container $container) {

            return new Router($container);
        });
    }
}