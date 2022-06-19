<?php

namespace Artificers\Supports\Illusion;

class Request extends Illusion {
    protected static function getIllusionAccessor(): string {
        return 'request';
    }
}