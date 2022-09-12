<?php

namespace Artificers\Foundation\Support\Registrar;

use Artificers\Support\ServiceRegister;
use Artificers\Treaties\Container\BindingException;
use Artificers\Treaties\Container\NotFoundException;
use Closure;
use ReflectionException;

class RouteServiceRegister extends ServiceRegister {
    public Closure|null $loadRoutesUsingCallback;

    /**
     * @throws ReflectionException
     * @throws NotFoundException
     * @throws BindingException
     */
    public function register(): void {
       $this->loadRoutes();

        $this->rXsiApp->booted(function($rXsiApp) {
            $rXsiApp['router']->getRoutes()->refreshNameList();
        });
    }

    public function boot(): void {
        // TODO
    }

    /**
     * @throws ReflectionException
     * @throws NotFoundException
     * @throws BindingException
     */
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
    protected function registerRoutes(Closure $routesCallback): static {
        $this->loadRoutesUsingCallback = $routesCallback;

        return $this;
    }
}