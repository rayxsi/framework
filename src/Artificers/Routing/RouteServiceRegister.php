<?php

namespace Artificers\Routing;

use Artificers\Container\Container;
use Artificers\Support\ServiceRegister;

class RouteServiceRegister extends ServiceRegister {
    public function register(): void {
        $this->registerRouter();
        $this->registerResponseFactory();
    }

    private function registerRouter() {
        $this->rXsiApp->singleton('router', function(Container $container) {
            return new Router($container);
        });
    }

    private function registerResponseFactory() {
        $this->rXsiApp->bind('response.factory', function(Container $container) {
            return new ResponseFactory($container);
        });
    }
}