<?php

namespace Artificers\Support\Illusion;

/**
* @method static basePath(string $path): string
 */

class RXsiApp extends Illusion {
    protected static function getIllusionAccessor(): string {

        return 'rXsiApp';
    }
}