<?php

namespace Artificers\Supports\Illusion;

/**
* @method static \Artificers\Supports\Illusion\View generate(): string
 */
class View extends Illusion {
    protected static function getIllusionAccessor(): string {

        return 'view';
    }
}