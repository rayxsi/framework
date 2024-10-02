<?php declare(strict_types=1);
namespace Artificers\Routing;

use Artificers\Container\Container;
use Artificers\Http\Request;
use Artificers\Routing\Events\RouteMatchedEvent;
use Artificers\Routing\Events\RoutingEvent;
use Artificers\Routing\Exception\NotFoundHttpException;
use Artificers\Support\Concern\AboutResponse;
use Artificers\Treaties\Events\EventDispatcherTreaties;
use Artificers\Treaties\Events\EventListenerProviderTreaties;
use Artificers\Utility\Ary;
use Closure;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Router class handle all about routing and prepare the response and send back to the http kernel.
 *
 *
 * @author Topu <toerso.mechanix@gmail.com>
 */
class Router {
    use AboutResponse;
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

    protected Route $currentRoute;

    public function __construct(Container $container = null) {
        $this->container = $container ?: new Container;
        $this->routes = new RouteCollection;

        $this->listener = $this->container['event.listener'];
        $this->dispatcher = $this->container['event.dispatcher'];
    }

    /**
     * Create route with get method.
     * @param string                    $uri        Set a URI to route.
     * @param callable|string|array     $action     Action handler for this route.
     * @return Route    Route instance.
     */
    public function get(string $uri, callable|string|array $action): Route {
        return $this->addRoute('GET', $uri, $action);
    }

    /**
     * Create route with post method.
     * @param string                    $uri        Set a URI to route.
     * @param callable|string|array     $action     Action handler for this route.
     * @return Route    Route instance.
     */
    public function post(string $uri, callable|string|array $action): Route {
        return $this->addRoute('POST', $uri, $action);
    }

    /**
     * Create route with put method.
     *
     * @param string                    $uri        Set a URI to route.
     * @param callable|string|array     $action     Action handler for this route.
     * @return Route    Route instance.
     */
    public function put(string $uri, callable|string|array $action): Route {
      return $this->addRoute('PUT', $uri, $action);
    }

    /**
     * Create route with delete method.
     *
     * @param string                    $uri        Set a URI to route.
     * @param callable|string|array     $action     Action handler for this route.
     * @return Route    Route instance.
     */
    public function delete(string $uri, callable|string|array $action): Route {
       return $this->addRoute('DELETE', $uri, $action);
    }

    /**
     * Fallback Route.
     *
     * @param callable $action  Action handler for fallback route.
     * @return Route    Route instance.
     */
    public function fallback(callable $action): Route {
        $placeholder = "fallback";

        return $this->addRoute('GET', "[{$placeholder}]", $action)->where($placeholder, '.*')->setName('fallback');
    }

    /**
     * Add route to route collection.
     *
     * @param string                $method    Http method.
     * @param string                $uri       URI
     * @param callable|string|array $action    Action handler for this route
     * @return Route Route instance.
     */
    protected function addRoute(string $method, string $uri, callable|string|array $action): Route {
        return $this->routes->add($this->createRoute($method, $uri, $action));
    }

    /**
     * Create Route.
     *
     * @param string                $method    Http method.
     * @param string                $uri       URI
     * @param callable|string|array $action    Action handler for this route
     * @return Route Route instance.
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
     * @param array $action     Array of action and handler that will concatenate with @ sign.
     * @return string           Concatenated string with @.
     */
    protected function concatActionWithHandler(array $action): string {
        return $action[0].'@'.$action[1];
    }

    /**
     * Generate new Route.
     *
     * @param string                    $method     Http method.
     * @param string                    $uri        Route uri.
     * @param callable|string|array     $action     Action handler.
     * @return Route                                Brand-new route.
     */
    protected function newRoute(string $method, string $uri, callable|string|array $action): Route {
        return (new Route($method, $uri, $action))->setRouter($this)->setContainer($this->container);
    }

    /**
     * Add prefix to uri.
     *
     * @param string    $uri    Route uri.
     * @return string           Prefixed uri.
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
     * @return SymfonyResponse
     * @throws NotFoundHttpException
     */
    public function resolve(Request $request): SymfonyResponse {
        if(!$this->container->isResolved('view')) {
            $this->initializeView();
        }

        return $this->dispatchToRoute($request);
    }

    /**
     * Prepare view engine.
     * @return void
     */
    private function initializeView(): void {
        $this->container['view'];
    }

    /**
     * Dispatch the Route.
     * @param Request $request
     * @return SymfonyResponse
     * @throws NotFoundHttpException
     */
    private function dispatchToRoute(Request $request): SymfonyResponse {
        return $this->executeRoute($request, $this->findRoute($request));
    }

    /**
     * Run the matched Route.
     * @param Request $request
     * @param Route $route
     * @return SymfonyResponse
     */
    private function executeRoute(Request $request, Route $route): SymfonyResponse {
        //dispatch route event if there set any.
        $this->dispatcher->dispatch(new RouteMatchedEvent($request, $route));

        //gather middleware for this route
        $this->gatherMiddlewares($route);

        return $this->container['dp']->get('Pipeline')
                ->send($request)
                ->through($this->getMiddlewarePriority())
                ->next(fn()=>$this->prepareResponse($request, $route->compile()));
    }

    /**
     * Arrange middleware based on priority.
     * @return array
     */
    protected function getMiddlewarePriority(): array {
        return array_reverse(Ary::merge($this->middlewares, $this->groupMiddlewares));
    }

    /**
     * Adding an event listener when route is matched.
     * @param Closure|string $listener
     * @return void
     */
    protected function matched(Closure|string $listener): void {
        $this->listener->addEventListener(RouteMatchedEvent::class, $listener);
    }

    /**
     * Find the Route from RouteCollection.
     *
     * @param Request $request
     * @return Route
     * @throws NotFoundHttpException
     */
    private function findRoute(Request $request): Route {
        $this->dispatcher->dispatch(new RoutingEvent($request));
        $this->currentRoute = $route = $this->routes->match($request);
        $route->setContainer($this->container);
        $this->container->setInstance(Route::class, $route);

        return $route;
    }

    /**
     * Adding an event listener when routing is started.
     * @param Closure|string $listener
     * @return void
     */
    public function matching(Closure|string $listener): void {
        $this->listener->addEventListener(RoutingEvent::class, $listener);
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

    protected function gatherMiddlewares($route): void {
        $middlewareContainer = $this->container->make('middleware');

        foreach($route->middleware as $item) {
            if(key_exists($item, $groupMw = $middlewareContainer->get('group'))) {
                $this->groupMiddlewares = Ary::merge($this->groupMiddlewares, $groupMw[$item]);
            }

            if(key_exists($item, $routeMw = $middlewareContainer->get('route'))) {
                $this->middlewares[] = $routeMw[$item];
            }
        }
    }

    /**
     * Refresh route collection.
     * @return void
     */
    public function refreshCollection(): void {
        $this->routes->refreshNameList();
    }

    public function __call(string $name, array $arguments) {
       if($name === 'makeGroup') {
           return (new RouteRegistrar($this, $this->routes))->prepareForGrouping($name, $arguments[0]??[]);
       }
    }
}