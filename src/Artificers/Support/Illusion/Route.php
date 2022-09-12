<?php

namespace Artificers\Support\Illusion;

use Closure;

/**
* @method static \Artificers\Routing\Route get(string $uri, callable|string|array $action): Route
* @method static \Artificers\Routing\Route post(string $uri, callable|string|array $action): Route
* @method static \Artificers\Routing\Route delete(string $uri, callable|string|array $action): Route
* @method static \Artificers\Routing\Route put(string $uri, callable|string|array $action): Route
* @method static \Artificers\Routing\Route makeGroup(array $properties=[]): void
 * @method static \Artificers\Routing\Router group(array $properties, Closure|array|string $routes): void
 */

class Route extends Illusion {
    protected static function getIllusionAccessor(): string {
       return 'router';
    }
}