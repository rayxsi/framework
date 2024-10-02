<?php

namespace Artificers\Routing;

use Artificers\Container\Container;
use Artificers\Http\Exception\NotFoundHttpException;
use Artificers\Http\Request;
use Artificers\Treaties\Container\BindingException;
use Artificers\Treaties\Container\NotFoundException;
use Artificers\Utility\Ary;
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

    private Request $request;

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
    public function setUri(string $exp): static {
        $this->uri = $exp;
        return $this;
    }

    public function bindRequest(Request $request): void {
        $this->request = $request;
    }

    public function getCurrentRequest(): Request {
        return $this->request;
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
     * @param Container $container
     * @return $this
     */
    public function setContainer(Container $container): static {
        $this->container = $container;

        return $this;
    }

    /**
     * Set middleware to this route.
     * @param array|string $middleware
     * @return $this
     */
    public function with(array|string $middleware): static {
        $middleware = is_array($middleware) ? $middleware : Ary::wrap($middleware);
        $this->properties['middleware'] = Ary::merge($this->properties['middleware'] ?? [], $middleware);
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

    //if anything occur unexpected then all the exception from here are to be not found exception.404.
    public function compile():mixed {
        try {
            return $this->container->call($this->properties['action'], $this->properties['args']);
        } catch (BindingException|\ReflectionException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    /**
    *Dynamically access properties.
     *
     */
    public function __get(string $key) {
        return $this->properties[$key];
    }
}