<?php

namespace Artificers\Routing;

class RouteFileRegistrar {
    public Router $router;

    public function __construct(Router $router) {

        $this->router = $router;
    }

    public function register($route): void {
        require_once $route;
    }
}