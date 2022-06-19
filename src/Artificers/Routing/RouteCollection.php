<?php

namespace Artificers\Network\Routing;

use Artificers\Network\Http\Request;

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
        $this->routeActionList[$route->getAction()] = $route;
        $this->addToLookUp($route);

        return $route;
    }

    private function addToLookUp(Route $route): void {
        if($name = $route->getName()) {
            $this->routeNameList[$name] = $route;
        }
    }

    private function refreshNameList(): void {
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
        return $this->routes[$method];
    }

    public function prefixSlash(string $uri): string {
        if($uri === '/')
            return $uri;

        return '/'.$uri;
    }

    /**
     * @param Request $request
     * @return Route|null
     */
    public function findRouteFromCollection(Request $request): ?Route {
        $this->refreshNameList();

        //1. We have to resolve request uri and method.
        $requestUri = urldecode($request->getRequestUri());
        $requestMethod = $request->getMethod();

        return $this->getMatchedRoute($requestMethod, $requestUri);
    }

    private function getMatchedRoute(string $method, string $targetUri): Route|null {
        $routes = $this->getRoutes($method);

        foreach($routes as $uri=>$route) {
            $uri = $this->prefixSlash($uri);

            if($uri !== '/') {
                $pattern = $this->transformRouteUriIntoRegexPattern($uri);

                if(preg_match_all($pattern, $targetUri, $matches)) {
                    $idx = 0;
                    for($i = 1; $i < count($matches); ++$i) {
                        $key = $route->properties['args'][$idx];
                        $route->properties['args'][$key] = $matches[$i][0];

                        unset($route->properties['args'][$idx++]);
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