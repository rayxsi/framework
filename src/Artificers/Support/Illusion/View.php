<?php

namespace Artificers\Support\Illusion;

/**
* @method static \Artificers\View\View generate(): void
 */
class View extends Illusion {
    protected static function getIllusionAccessor(): string {

        return 'view';
    }
}