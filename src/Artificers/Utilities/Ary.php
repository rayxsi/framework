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

    /**
     * Check is array or not.
     *
     * @param $array
     * @return bool
     */
    public static function isArr($array): bool {
        return is_array($array);
    }

    /**
     * Change array key case.
     *
     * @param array $array
     * @param $case
     * @return array
     */
    public  static function changeKeyCase(array $array, $case): array {
        return array_change_key_case($array, $case);
    }
}