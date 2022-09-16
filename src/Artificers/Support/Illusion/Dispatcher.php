<?php
declare(strict_types=1);
namespace Artificers\Support\Illusion;

use Artificers\Design\Patterns\Illusion;

/**
* @method \Artificers\Events\Dispatcher\EventDispatcher dispatch(object $event): EventTreaties
 */
class Dispatcher extends Illusion {
    protected static function getIllusionAccessor(): string {
       return "event.dispatcher";
    }
}