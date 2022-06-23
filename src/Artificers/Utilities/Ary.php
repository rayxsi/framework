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

    /**
     * @param $array
     * @param $key
     * @param $value
     * @return mixed
     */
    public static function set(&$array, $key, $value): mixed {
        //if key is null then just assign the value in array and return.
        if(is_null($key)) {
            return $array = $value;
        }

        //key can be string with dot notation. Then we have to handle it manually.
        $keys = explode('.', $key);

        foreach($keys as $idx=>$key) {
            // if $keys hold single key then we need to break and assign value into it
            if(count($keys) === 1) {
                break;
            }

            //if array doesn't have this key and is not array then we create empty array
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];

            unset($keys[$idx]);
        }

        return $array[array_shift($keys)] = $value;
    }

    /**
     * @param $array
     * @param array|string $key
     * @return mixed
     */
    public static function get(&$array, array|string $key): mixed {
       $keys = is_array($key) ? $key : explode('.', $key);

       foreach($keys as $idx => $key) {
           if(count($keys) === 1) {
               break;
           }

           $array = &$array[$key];

           unset($keys[$idx]);
       }

       return $array[array_shift($keys)] ?? null;
    }

    public static function filter(array $array, $callback, $flag=''): array {
        if($flag === 'both') {
            return array_filter($array, $callback, ARRAY_FILTER_USE_KEY);
        }

        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }
}