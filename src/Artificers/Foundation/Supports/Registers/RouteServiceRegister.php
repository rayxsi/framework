<?php

namespace Artificers\Foundation\Supports\Registers;

use Artificers\Supports\ServiceRegister;
use Closure;

class RouteServiceRegister extends ServiceRegister {
    public Closure|null $loadRoutesUsingCallback;

    public function register(): void {
       $this->loadRoutes();

        $this->rXsiApp->booted(function($rXsiApp) {
            $rXsiApp['router']->getRoutes()->refreshNameList();
        });
    }

    public function boot(): void {
        // TODO
    }

    protected function loadRoutes(): void {
        if(!is_null($this->loadRoutesUsingCallback)) {
            $this->rXsiApp->call($this->loadRoutesUsingCallback);
        }
    }

    /**
     * Register the callback that will be used to load the application's routes.
     *
     * @param  Closure  $routesCallback
     * @return $this
     */
    protected function registerRoutes(Closure $routesCallback): self {
        $this->loadRoutesUsingCallback = $routesCallback;

        return $this;
    }
}