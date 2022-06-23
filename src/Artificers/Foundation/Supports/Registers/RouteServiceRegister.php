<?php

namespace Artificers\Foundation\Supports\Registers;

use Artificers\Supports\ServiceRegister;
use Closure;

class RouteServiceRegister extends ServiceRegister {
    public Closure $loadRoutesUsingCallback;

    public function register(): void {
       $this->loadRoutes();

        $this->rXsiApp->booted(function($rXsiApp) {
            $this->rXsiApp['router']->getRoutes()->refreshNameList();
        });
    }

    public function boot(): void {
        // TODO
    }

    protected function loadRoutes(): void {

    }

    /**
     * Register the callback that will be used to load the application's routes.
     *
     * @param  Closure  $routesCallback
     * @return $this
     */
    protected function routes(Closure $routesCallback): self {
        $this->loadRoutesUsingCallback = $routesCallback;

        return $this;
    }
}