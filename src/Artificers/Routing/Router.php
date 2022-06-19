<?php
declare(strict_types=1);

namespace Artificers\Routing;

use ArrayIterator;
use Artificers\Container\Container;
use Artificers\Http\Request;
use Artificers\Routing\Events\RouteMatchedEvent;
use Artificers\Routing\Exception\RouteNotFoundException;
use Artificers\Treaties\Events\EventDispatcherTreaties;
use Artificers\Treaties\Events\EventListenerProviderTreaties;
use Artificers\Utilities\Ary;
use Closure;

class Router {
    private Container $container;
    private RouteCollection $routes;

    private EventListenerProviderTreaties $listener;
    private EventDispatcherTreaties $dispatcher;

    public function __construct(Container $container = null) {
        $this->container = $container ?: new Container;
        $this->routes = new RouteCollection;

        $this->listener = $this->container['event.listener'];
        $this->dispatcher = $this->container['event.dispatcher'];
    }

    /**
     * @param string $uri
     * @param callable|string|array $action
     * @return Route
     */
    public function get(string $uri, callable|string|array $action): Route {
        return $this->addRoute('GET', $uri, $action);
    }

    /**
     * @param string $uri
     * @param callable|string|array $action
     * @return Route
     */
    public function post(string $uri, callable|string|array $action): Route {
        return $this->addRoute('POST', $uri, $action);
    }

    /**
     * @param string $uri
     * @param callable|string|array $action
     * @return Route
     */
    public function put(string $uri, callable|string|array $action): Route {
      return $this->addRoute('PUT', $uri, $action);
    }

    /**
     * @param string $uri
     * @param callable|string|array $action
     * @return Route
     */
    public function delete(string $uri, callable|string|array $action): Route {
       return $this->addRoute('DELETE', $uri, $action);
    }

    protected function addRoute(string $method, string $uri, callable|string|array $action): Route {
        return $this->routes->add($this->createRoute($method, $uri, $action));
    }

    /**
     * @param string $method
     * @param string $uri
     * @param callable|string|array $action
     * @return Route
     */
    protected function createRoute(string $method, string $uri, callable|string|array $action): Route {
        if(Ary::isArr($action)) {
            $action = $this->concatActionWithHandler($action);
        }

        return new Route($method, $this->stripSlash($uri), $action);
    }

    /**
     * @param array $action
     * @return string
     */
    protected function concatActionWithHandler(array $action): string {
        return $action[0].'@'.$action[1];
    }

    /**
     * @param string $uri
     * @return string
     */
    private function stripSlash(string $uri): string {
        $strippedUri = trim($uri, '/');
        return empty($strippedUri) ? $uri : $strippedUri;
    }

    /**
     * @throws RouteNotFoundException
     */
    public function resolveWithRouter(Request $request) {

        $route = $this->routes->findRouteFromCollection($request);

        if(!$route) {
            throw  new RouteNotFoundException("Route not found.");
        }

        $this->container['event.listener']->addEventListener('route.matched', $route->properties['controller'], $route->properties['args']);
        $this->container['event.dispatcher']->dispatch(new RouteMatchedEvent($request, $route));
    }
}