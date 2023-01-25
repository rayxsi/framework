<?php

namespace Artificers\Routing\Events;

use Artificers\Events\Event;
use Artificers\Http\Request;

class RoutingEvent extends Event {
    public const type = "routing";
    public Request $request;

    public function __construct(Request $request) {
        $this->request = $request;
    }
}