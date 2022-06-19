<?php

namespace Artificers\Network\Routing\Events;

use Artificers\Events\Event;
use Artificers\Network\Http\Request;
use Artificers\Network\Routing\Route;

class RouteMatchedEvent extends Event {
    public const type = 'route.matched';
    public Request $request;
    public Route $route;

    public function __construct(Request $request, Route $route) {
        $this->request = $request;
        $this->route = $route;
    }

}