<?php

namespace Artificers\Support\Illusion;

use Artificers\Treaties\Events\EventTreaties;

/**
* @method \Artificers\Event\Dispatcher\EventDispatcher dispatch(object $event): EventTreaties
 */
class Dispatcher extends Illusion {
    protected static function getIllusionAccessor(): string {
       return "event.dispatcher";
    }
}