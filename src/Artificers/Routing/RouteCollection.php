<?php

namespace Artificers\Routing;

use Artificers\Http\Request;
use Artificers\Routing\Exception\NotFoundHttpException;
use Closure;

class RouteCollection {
    /**
    *Route storage.
     *
     */
    private array $routes = [];

    /**
    *Route name storage
     *
     */
    private array $routeNameList = [];

    private array $routeActionList = [];

    private array $allRoutes = [];

    public function add(Route $route): Route {
        $this->routes[$route->getMethod()][$route->getUri()] = $route;
        $this->allRoutes[$route->getUri()] = $route;
        $this->addToRouteActionList($route);
        $this->addToLookUp($route);

        return $route;
    }

    private function addToRouteActionList(Route $route): void {
        $action = $route->getAction();

        if(!$action instanceof Closure) {
            $this->routeActionList[$action] = $route;
        }
    }

    private function addToLookUp(Route $route): void {
        if($name = $route->getName()) {
            $this->routeNameList[$name] = $route;
        }
    }

    public function refreshNameList(): void {
        foreach($this->allRoutes as $route) {
            $this->addToLookUp($route);
        }
    }

    public function getRoute(string $method, string $uri): mixed {
        return $this->routes[$method][$uri] ?? null;
    }

    public function getRouteByName(string $name): mixed {

        return $this->routeNameList[$name] ?? null;
    }

    public function getRoutes(string $method): array {
        return $this->routes[$method] ?? [];
    }

    public function prefixSlash(string $uri): string {
        if($uri === '/')
            return $uri;

        return '/'.$uri;
    }

    /**
     * @param Request $request
     * @return Route
     * @throws NotFoundHttpException
     */
    public function match(Request $request): Route {
        //1. We have to resolve request uri and method.
        $requestUri = $request->getRequestPath();
        $requestMethod = $request->getMethod();

        //find the route with request method and request uri.
        $route = $this->getMatchedRoute($requestMethod, $requestUri);

        //if route is null then find the route with name.
        if(is_null($route)) {
            $route = $this->getRouteByName(trim($requestUri, '/'));
        }

        return $this->handleMatchedRoute($request, $route);
    }

    /**
     * @param Request $request
     * @param Route|null $route
     * @return Route
     * @throws NotFoundHttpException
     */
    private function handleMatchedRoute(Request $request, Route|null $route): Route {
        if(!is_null($route)) {
            $route->bindRequest($request);
            return $route;
        }
        throw new NotFoundHttpException;
    }

    /**
     * Find out the matched route.
     * @param string $method
     * @param string $targetUri
     * @return Route|null
     */
    private function getMatchedRoute(string $method, string $targetUri): ?Route {
        $routes = $this->getRoutes($method);

        foreach($routes as $uri=>$route) {
            $uri = $this->prefixSlash($uri);
            if($uri !== '/') {
                $pattern = $this->transformRouteUriIntoRegexPattern($uri);

                if(preg_match_all($pattern, $targetUri, $matches)) {
                    $idx = 0;
                    for($i = 1; $i < count($matches); ++$i) {
                        $key = $route->getProperties()['args'][$idx];
                        $route->setArgs($key, $matches[$i][0]);
                        $route->unsetArgs($idx);
                        $idx = $idx + 1;
                    }
                    return $route;
                }
            }

            if($uri === '/' && $uri === $targetUri)
                return $route;
        }

        return null;
    }

    private function transformRouteUriIntoRegexPattern(string $routeUri): string {
        $transformParamToRegex = preg_replace_callback('/:\w+|\[:\w+(.*?)]/', fn($m) => empty($m[1]) ? "(\w+)" : "($m[1]+)", $routeUri);
        return '/'.preg_replace_callback('/\//', fn($m) => "\/", $transformParamToRegex.'/?$').'/';
    }
}