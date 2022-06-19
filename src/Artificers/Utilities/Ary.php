<?php

namespace Artificers\Utilities;

class Ary {
    /**
     *Check array key exists.
     *Return true or false.
     *
     * @param $key
     * @param array $array
     * @return bool
     */
    public static function keyExists($key, array $array): bool {
        if(array_key_exists($key, $array))
            return true;

        return false;
    }

    public static function isArr($array): bool {
        return is_array($array);
    }
}