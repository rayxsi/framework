<?php

namespace Artificers\Events;

use Artificers\Events\Dispatcher\EventDispatcher;
use Artificers\Events\Listener\EventListenerProvider;
use Artificers\Supports\ServiceRegister;

class EventServiceRegister extends ServiceRegister {
    public function register(): void {
        $this->registerListener();
        $this->registerEventDispatcher();
    }

    private function registerListener(): void {
        $this->rXsiApp->singleton('event.listener', function($container) {
            return new EventListenerProvider();
        });

        return;
    }

    private function registerEventDispatcher(): void {
        $listener = $this->rXsiApp['event.listener'];

        $this->rXsiApp->singleton('event.dispatcher', function($container) use($listener) {

            return new EventDispatcher($listener, $container);
        });
        return;
    }
}