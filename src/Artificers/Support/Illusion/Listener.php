<?php

namespace Artificers\Support\Illusion;
/**
* @method static \Artificers\Events\Listener\EventListenerProvider addEventListener(string $type, callable|string|array $listeners, array $params = []): static
* @method static \Artificers\Events\Listener\EventListenerProvider getListenersForEvent(object $event): iterable
* @method static \Artificers\Events\Listener\EventListenerProvider getAllParams(): array
* @method static \Artificers\Events\Listener\EventListenerProvider removeEventListener(string $type): static
 */
class Listener extends Illusion {
    protected static function getIllusionAccessor(): string {
        return 'event.listener';
    }
}