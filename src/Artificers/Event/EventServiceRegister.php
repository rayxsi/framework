<?php

namespace Artificers\Event;

use Artificers\Event\Dispatcher\EventDispatcher;
use Artificers\Event\Listener\EventListenerProvider;
use Artificers\Support\ServiceRegister;

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