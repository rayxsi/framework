<?php
declare(strict_types=1);
namespace Artificers\Routing;

use Artificers\Container\Container;
use Artificers\Http\Request;
use Artificers\Http\Response;
use Artificers\Routing\Events\RouteMatchedEvent;
use Artificers\Treaties\Events\EventDispatcherTreaties;
use Artificers\Treaties\Events\EventListenerProviderTreaties;
use Artificers\Utility\Ary;
use Closure;

class Router {
    /**
     * Container.
     *
     * @var Container
     */
    private Container $container;


    /**
     * Route collection.
     *
     * @var RouteCollection
     */
    private RouteCollection $routes;

    /**
     * Event listener.
     *
     * @var EventListenerProviderTreaties|mixed
     */
    private EventListenerProviderTreaties $listener;

    /**
     * Event dispatcher.
     *
     * @var EventDispatcherTreaties|mixed
     */
    private EventDispatcherTreaties $dispatcher;

    /**
     * Stack of group props.
     *
     * @var array
     */
    public array $groupStackProps = [];

    protected array $middlewares = [];
    protected array $groupMiddlewares = [];

    public function __construct(Container $container = null) {
        $this->container = $container ?: new Container;
        $this->routes = new RouteCollection;

        $this->listener = $this->container['event.listener'];
        $this->dispatcher = $this->container['event.dispatcher'];
    }

    /**
     * Create route with get method.
     * @param string $uri
     * @param callable|string|array $action
     * @return Route
     */
    public function get(string $uri, callable|string|array $action): Route {
        return $this->addRoute('GET', $uri, $action);
    }

    /**
     * Create route with post method.
     * @param string $uri
     * @param callable|string|array $action
     * @return Route
     */
    public function post(string $uri, callable|string|array $action): Route {
        return $this->addRoute('POST', $uri, $action);
    }

    /**
     * Create route with put method.
     *
     * @param string $uri
     * @param callable|string|array $action
     * @return Route
     */
    public function put(string $uri, callable|string|array $action): Route {
      return $this->addRoute('PUT', $uri, $action);
    }

    /**
     * Create route with delete method.
     *
     * @param string $uri
     * @param callable|string|array $action
     * @return Route
     */
    public function delete(string $uri, callable|string|array $action): Route {
       return $this->addRoute('DELETE', $uri, $action);
    }

    /**
     * Fallback Route.
     *
     * @param callable $action
     * @return Route
     */
    public function fallback(callable $action): Route {
        $placeholder = "fallback";

        return $this->addRoute('GET', "[{$placeholder}]", $action)->where($placeholder, '.*')->setName('fallback');
    }

    /**
     * Add route to route collection.
     *
     * @param string $method
     * @param string $uri
     * @param callable|string|array $action
     * @return Route
     */
    protected function addRoute(string $method, string $uri, callable|string|array $action): Route {
        return $this->routes->add($this->createRoute($method, $uri, $action));
    }

    /**
     * Create Route.
     *
     * @param string $method
     * @param string $uri
     * @param callable|string|array $action
     * @return Route
     */
    protected function createRoute(string $method, string $uri, callable|string|array $action): Route {
        if(Ary::isArr($action)) {
            $action = $this->concatActionWithHandler($action);
        }

        $route = $this->newRoute($method, $this->addPrefix($uri), $action);

        if($this->hasGroupStackProps()) {
          $this->mergeGroupStackPropsToRouteProps($route);
        }

        return $route;
    }

    /**
     * Concat controller with action handler.
     *
     * @param array $action
     * @return string
     */
    protected function concatActionWithHandler(array $action): string {
        return $action[0].'@'.$action[1];
    }

    /**
     * Generate new Route.
     *
     * @param string $method
     * @param string $uri
     * @param callable|string|array $action
     * @return Route
     */
    protected function newRoute(string $method, string $uri, callable|string|array $action): Route {
        return (new Route($method, $uri, $action))->setRouter($this)->setContainer($this->container);
    }

    /**
     * Add prefix to uri.
     *
     * @param string $uri
     * @return string
     */
    protected function addPrefix(string $uri): string {
        $uri = trim(trim($this->getLastGroupStackPrefix(), '/').'/'.trim($uri, '/'), '/');

        return $uri !== '' ? $uri : '/';
    }

    /**
     * Get the last group prefix.
     *
     * @return string
     */
    protected function getLastGroupStackPrefix(): string {
        return $this->hasGroupStackProps() ? end($this->groupStackProps)['prefix'] ?? '' : '';
    }

    /**
     * Merge the current group props with the new Route props.
     *
     * @param Route $route
     * @return void
     */
    protected function mergeGroupStackPropsToRouteProps(Route $route): void {
        $route->setProperties($this->mergeWithLastGroupProps($route->getProperties(), false));
    }

    /**
     * Resolve Request with the Router.
     *
     * @param Request $request
     * @return Response
     */
    public function resolve(Request $request): Response {
        if(!$this->container->isResolved('view')) {
            $this->initializeView();
        }

        return $this->dispatchToRoute($request);
    }

    /**
     * Prepare view engine.
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
    private function dispatchToRoute(Request $request): Response {
        return $this->runRoute($request, $this->findRoute($request));
    }

    /**
     * Run the matched Route.
     *
     * @param $request
     * @param Route|null $route
     * @return Response
     */
    private function runRoute($request, Route|null $route): Response {
        if(is_null($route)) {
            return $this->prepareResponse($route);
        }

        $this->matched($route);

        $this->container['event.dispatcher']->dispatch(new RouteMatchedEvent($request, $route));

        $this->gatherMiddlewares();

        return $this->prepareResponse($route);
    }

    /**
     * Prepare the correct Response.
     *
     * @param $route
     * @return Response
     */
    protected function prepareResponse($route): Response {
        $content = $this->getViewContent();

        return match($route) {
            null => response("<h1>404|NOT FOUND</h1>", 404,  ["Content-Type" => "text/html"]),
            $this->isFallback($route) =>  response("<h1>404|NOT FOUND</h1>", 404,  ["Content-Type" => "text/html"]),
            default => response($content, 200,  ["Content-Type" => "text/html"])
        };
    }

    /**
     * Get view content from cache.
     *
     * @return string
     */
    protected function getViewContent(): string {
        $view = $this->container['cache']->get('view');

        $content = $view->get();

        //clean view
        $view->clean();

        return $content;
    }

    /**
     * Set matched event.
     *
     * @param $route
     * @return void
     */
    protected function matched($route): void {
        $this->container['event.listener']->addEventListener('route.matched', $route->getProperties()['action'], $route->getProperties()['args']);
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
     * Make Route Group.
     *
     * @param array $properties
     * @param Closure|array|string $routes
     * @return void
     */
    public function group(array $properties, Closure|array|string $routes): void {
        foreach(Ary::wrap($routes) as $groupRoutes) {
            $this->updateGroupPropsStack($properties);

            $this->makeRoutes($groupRoutes);
            Ary::pop($this->groupStackProps);
        }
    }

    /**
     * Update the group stack props.
     *
     * @param array $props
     * @return void
     */
    protected function updateGroupPropsStack(array $props): void {
        //If group props stack already having props then we assume current group as subgroup and merge with the last stack elements.
        if($this->hasGroupStackProps()) {
            $props = $this->mergeWithLastGroupProps($props);
        }

        $this->groupStackProps[] = $props;
    }


    /**
     * Merge current group props with the last group props of the stack.
     *
     * @param array $props
     * @param bool $prependWithExistingPrefix
     * @return array
     */
    protected function mergeWithLastGroupProps(array $props, bool $prependWithExistingPrefix = true): array {
       return RouteGroup::mergeProps($props, end($this->groupStackProps), $prependWithExistingPrefix);
    }

    /**
     * Load Routes.
     *
     * @param $routes
     * @return void
     */
    private function makeRoutes($routes): void {
        if(is_string($routes)) {
            (new RouteFileRegistrar($this))->register($routes);
            return;
        }

        $routes($this);
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

    /**
     * Get Route collection.
     *
     * @return RouteCollection
     */
    public function getRoutes(): RouteCollection {
        return $this->routes;
    }

    /**
     * Check group stack props is empty or not.
     *
     * @return bool
     */
    public function hasGroupStackProps(): bool {

        return !empty($this->groupStackProps);
    }

    protected function gatherMiddlewares(): void {
        $middleware = $this->container->make('middleware');

        $this->middlewares = $middleware->get('route');
        $this->groupMiddlewares = $middleware->get('group');
    }

    public function __call(string $name, array $arguments) {
       if($name === 'makeGroup') {
           return (new RouteRegistrar($this, $this->routes))->prepareForGrouping($name, $arguments[0]??[]);
       }
    }
}