<?php

namespace Artificers\Support\Helper;

class Helper {
    public static function makeInstance($class): object {
        return new $class;
    }
}