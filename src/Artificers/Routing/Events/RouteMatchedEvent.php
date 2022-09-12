<?php

namespace Artificers\Routing\Events;

use Artificers\Event\Event;
use Artificers\Http\Request;
use Artificers\Routing\Route;

class RouteMatchedEvent extends Event {
    public const type = 'route.matched';
    public Request $request;
    public Route $route;

    public function __construct(Request $request, Route $route) {
        $this->request = $request;
        $this->route = $route;
    }

}