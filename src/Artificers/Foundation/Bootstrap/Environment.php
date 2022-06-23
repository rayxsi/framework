<?php

namespace Artificers\Foundation\Bootstrap;

use Artificers\Treaties\Bootstrap\BootstrapListenerTreaties;

class Environment implements BootstrapListenerTreaties {
    public function load($event): void {
        $event->rXsiApp->environment();
        $event->rXsiApp->errorHandler();
    }
}