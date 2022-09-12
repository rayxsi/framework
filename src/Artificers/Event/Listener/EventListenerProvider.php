<?php

namespace Artificers\Event\Listener;

use Artificers\Event\ListenerNotFoundException;
use Artificers\Treaties\Events\EventListenerProviderTreaties;
use Artificers\Utility\Ary;

class EventListenerProvider implements EventListenerProviderTreaties {

    private array $eventMapToListeners = [];
    private array $parameters = [];

    /**
     * @inheritDoc
     * @throws ListenerNotFoundException
     */
    public function getListenersForEvent(object $event): iterable {
        $nameSpaceTyped = get_class($event);
        $type = $event::type;

        if(empty($type) && Ary::keyExists($nameSpaceTyped, $this->eventMapToListeners)) {
            return $this->eventMapToListeners[$nameSpaceTyped];
        }

        if(!Ary::keyExists($type, $this->eventMapToListeners))
            throw new ListenerNotFoundException("Listener not found for the following key {$type}");


        return $this->eventMapToListeners[$type];
    }

    /**
     *Adds event listener.
     * @param string $type
     * @param callable|string|array $listeners
     * @param array $params
     * @return self
     */
    public function addEventListener(string $type, callable|string|array $listeners, array $params = []): static {
        $this->eventMapToListeners[$type][] = $listeners;
        $this->parameters = $params;

        return $this;
    }

    /**
     * Give all the listeners params.
     *
     * @return array
     */
    public function getAllParams(): array {
        return $this->parameters;
    }

    /**
    *Removes event listener.
     * @param string $type
     * @return self
     */
    public function removeEventListener(string $type): static {
        if(Ary::keyExists($type, $this->eventMapToListeners))
            unset($this->eventMapToListeners[$type]);

        return $this;
    }
}