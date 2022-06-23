<?php
declare(strict_types=1);

namespace Artificers\Routing;

use ArrayIterator;
use Artificers\Container\Container;
use Artificers\Http\Request;
use Artificers\Http\Response;
use Artificers\Routing\Events\RouteMatchedEvent;
use Artificers\Routing\Exception\RouteNotFoundException;
use Artificers\Supports\Illusion\View;
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

        //add fallback router by default.
        $this->fallback(function() {View::generate();});
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

    public function fallback(callable $action): Route {
        $placeholder = "fallback";

        return $this->addRoute('GET', "[{$placeholder}]", $action)->where($placeholder, '.*')->name('fallback');
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

        return (new Route($method, $this->stripSlash($uri), $action))->setRouter($this)->setContainer($this->container);
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
     * @param Request $request
     * @return Response
     */
    public function resolveWithRouter(Request $request): Response {
        if(!$this->container->isResolved('view')) {
            $this->initializeView();
        }

        return $this->dispatchRoute($request);
    }

    /**
     * prepare view engine.
     *
     * @return void
     */
    private function initializeView(): void {
        $this->container['view'];
    }

    /**
     * Dispatch the Route.
     *
     * @param Request $request
     * @return Response
     */
    private function dispatchRoute(Request $request): Response {
        return $this->runRoute($request, $this->findRoute($request));
    }

    /**
     * Run the matched Route.
     *
     * @param $request
     * @param Route $route
     * @return Response
     */
    private function runRoute($request, Route $route): Response {
        $this->matched($route);

        $this->container['event.dispatcher']->dispatch(new RouteMatchedEvent($request, $route));

        $view = $this->container['cache']->get('view');

        $content = "";

        if($view) {
            $content = $view->get();
            //clean view
            $view->clean();
        }

        if($this->isFallback($route)) {
            return response($content, 404,  ["Content-Type" => "text/html"]);
        }

        return response($content, 200,  ["Content-Type" => "text/html"]);
    }

    /**
     * Set matched event.
     *
     * @param $route
     * @return void
     */
    protected function matched($route): void {
        $this->container['event.listener']->addEventListener('route.matched', $route->properties['controller'], $route->properties['args']);
    }

    /**
     * Find the Route from RouteCollection.
     *
     * @param Request $request
     * @return Route|null
     */
    private function findRoute(Request $request): ?Route {

        return $this->routes->match($request);
    }

    /**
     * Check if route is fallback route.
     *
     * @param Route $route
     * @return bool
     */
    private function isFallback(Route $route): bool {
        return $route->getUri() === '.*';
    }

    public function getRoutes(): RouteCollection {
        return $this->routes;
    }
}