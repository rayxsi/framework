<?php

namespace Artificers\Routing;

use Closure;

class Route {
    /**
    *Route name.
     *
     */
    public array $properties = [];

    /**
    *URI.
     *
     */
    private string $uri;

    /**
    *Request method.
     *
     */
    private string $method;

    public function __construct(string $method, string $uri, Closure|string $action) {
        $this->method = $method;
        $this->uri = $uri;
        $this->properties['controller'] = $action;

        $this->setToUriArgList();
    }



    /**
    *Get the route name.
     *
     * @return mixed
     */
    public function getName(): mixed {
        return $this->properties['as'] ?? null;
    }

    /**
     *Set the route name.
     *
     * @param string $name
     * @return self
     */
    public function name(string $name): self {
       $this->properties['as'] = isset($this->properties['as']) ? $this->properties['as'].$name : $name;

       return $this;
    }

    /**
     *Get the route method.
     *
     * @return string
     */
    public function getMethod(): string {
        return $this->method;
    }

    /**
     *Get the route path.
     *
     * @return string
     */
    public function getUri(): string {
        return $this->uri;
    }

    /**
     *Get the route action.
     *
     * @return Closure|string
     */
    public function getAction(): Closure|string {
        return $this->properties['controller'];
    }

    /**
     * @return Closure|string
     */
    public function getActionHandler(): Closure|string {
        if(is_string($controller = $this->properties['controller'])) {
            $actionProp = explode('@', $controller);

            return $actionProp[1];
        }

        return $this->properties['controller'];
    }

    /**
     * Set uri args to uriArgs array.
     *
     * @return void
     */
    private function setToUriArgList(): void {
        preg_match_all('/:(\w+)/', $this->uri, $m);
        $this->properties['args'] = $m[1];
    }

    /**
     * Return all the uri params.
     *
     * @return array
     */
    public function getUriParams(): array {
        return $this->properties['args'];
    }

    /**
    *Dynamically access properties.
     *
     */
    public function __get(string $key) {
        return $this->properties[$key];
    }
}