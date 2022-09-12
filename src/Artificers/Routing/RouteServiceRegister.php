<?php

namespace Artificers\Routing;

use Artificers\Container\Container;
use Artificers\Support\ServiceRegister;

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