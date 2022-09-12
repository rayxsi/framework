<?php

namespace Artificers\Support\Illusion;

class Request extends Illusion {
    protected static function getIllusionAccessor(): string {
        return 'request';
    }
}