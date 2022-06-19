<?php

namespace Artificers\Supports\Illusion;

/**
* @method static \Artificers\Routing\Route get(string $uri, callable|string|array $action): Route
* @method static \Artificers\Routing\Route post(string $uri, callable|string|array $action): Route
* @method static \Artificers\Routing\Route delete(string $uri, callable|string|array $action): Route
* @method static \Artificers\Routing\Route put(string $uri, callable|string|array $action): Route
 */

class Route extends Illusion {
    protected static function getIllusionAccessor(): string {
       return 'router';
    }
}