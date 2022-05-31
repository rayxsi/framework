<?php

namespace Artificers\Supports\Helper;

class Helper {
    public static function makeInstance($class): object {
        return new $class;
    }
}