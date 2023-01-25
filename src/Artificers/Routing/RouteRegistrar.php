<?php

namespace Artificers\Routing;

use Closure;
use InvalidArgumentException;


class RouteRegistrar {
    protected Router $router;
    protected RouteCollection $routes;
    protected array $properties = [];

    private array $allowedProps = ['name', 'middleware', 'prefix', 'action'];

    public function __construct(Router $router, RouteCollection $routes) {
        $this->router = $router;
        $this->routes = $routes;
    }

    /**
     * @param string $method
     * @param array $properties
     * @return RouteRegistrar
     */
    public function prepareForGrouping(string $method, array $properties): static {
        if(!empty($properties)) {
            $this->checkForValidProps($properties);
        }

        $this->$method($properties);

        return $this;
    }

    protected function makeGroup($props=[]): void {
        $this->properties = $props;
    }

    protected function checkForValidProps($properties): void {
        $props = array_keys($properties);

        foreach($props as $prop) {
           if(!in_array($prop, $this->allowedProps)) {
               throw new InvalidArgumentException("Undefined property [{$prop}]. Route accept ['name', 'middleware', 'prefix', 'action'] as properties.");
           }
        }
    }

    /**
     * @param Closure|string $callback
     * @return void
     */
    public function group(Closure|string $callback): void {
        $this->router->group($this->properties, $callback);
    }
}