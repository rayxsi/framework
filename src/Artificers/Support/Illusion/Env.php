<?php

namespace Artificers\Support\Illusion;

/**
* @method static \Artificers\Foundation\Environment\Env collect(string $key)
 */
class Env extends Illusion {
    protected static function getIllusionAccessor(): string {
        return 'env';
    }
}