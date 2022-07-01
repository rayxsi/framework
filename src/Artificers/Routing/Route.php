<?php

namespace Artificers\Routing;

use Artificers\Container\Container;
use Closure;

class Route {
    /**
    *Route properties.
     *
     * @var array $properties
     */
    private array $properties = [];

    /**
    *Route uri.
     *
     * @var string $uri
     */
    private string $uri;

    /**
    *Request method.
     *
     * @var string $method
     */
    private string $method;

    /**
     * Router.
     *
     * @var Router
     */
    private Router $router;

    /**
     * Container.
     *
     * @var Container
     */
    private Container $container;

    public function __construct(string $method, string $uri, Closure|string $action) {
        $this->method = $method;
        $this->uri = $uri;
        $this->properties['action'] = $action;
        $this->setToUriArgList();
    }

    /**
    *Get the route name.
     *
     * @return mixed
     */
    public function getName(): mixed {
        return $this->properties['name'] ?? null;
    }

    /**
     *Set the route name.
     *
     * @param string $name
     * @return self
     */
    public function setName(string $name): self {
       $this->properties['name'] = isset($this->properties['name']) ? $this->properties['name'].$name : $name;

       return $this;
    }

    /**
     * Set Route Properties.
     *
     * @param array $props
     * @return void
     */
    public function setProperties(array $props): void {
        $this->properties = $props;
    }

    /**
     * Get Route properties.
     *
     * @return array
     */
    public function getProperties(): array {
        return $this->properties;
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
     * Set URI.
     *
     * @param string $exp
     * @return $this
     */
    public function setUri(string $exp): self {
        $this->uri = $exp;
        return $this;
    }

    /**
     *Get the route action.
     *
     * @return Closure|string
     */
    public function getAction(): Closure|string {
        return $this->properties['action'];
    }

    /**
     * Get action method.
     *
     * @return Closure|string
     */
    public function getActionHandler(): Closure|string {
        if(is_string($controller = $this->properties['action'])) {
            $actionProp = explode('@', $controller);

            return $actionProp[1];
        }

        return $this->properties['action'];
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

    public function where(string|array $placeholder, string $exp = null): self {
        foreach($this->parseWhereClause($placeholder, $exp) as $name=>$expression) {
            $this->properties['where'][$name] = $expression;
        }

        return $this;
    }

    /**
     * Wrap the where clause.
     *
     * @param string|array $placeholder
     * @param string $exp
     * @return string[]
     */
    protected function parseWhereClause(string|array $placeholder, string $exp):  array {
        return is_array($placeholder) ? $placeholder : [$placeholder=>$exp];
    }

    /**
     * Set Router to route.
     *
     * @param Router $router
     * @return $this
     */
    public function setRouter(Router $router): static {
        $this->router = $router;

        return $this;
    }

    /**
     * Set container to route.
     *
     * @param Container $container
     * @return $this
     */
    public function setContainer(Container $container): static {
        $this->container = $container;

        return $this;
    }

    /**
     * Set middleware.
     *
     * @param array|string|null $middleware
     * @return $this
     */
    public function middleware(array|string|null $middleware): static {
        $this->properties['middleware'] = $middleware;

        return $this;
    }

    /**
     * Set prefix.
     *
     * @param string $prefix
     * @return $this
     */
    public function prefix(string $prefix = ""): static {
        $this->properties['prefix'] = trim($prefix, '/');

        return $this;
    }

    public function setArgs($key, $value): void {
        $this->properties['args'][$key] = $value;
    }

    public function unsetArgs($key): void {
        unset($this->properties['args'][$key]);
    }

    /**
    *Dynamically access properties.
     *
     */
    public function __get(string $key) {
        return $this->properties[$key];
    }
}