<?php

namespace Artificers\Utility;

class Str {
    /**
     * Check for a sub-string inside a string.
     * @param string $haystack
     * @param string|array $needles
     * @param bool $ignore_case
     * @return bool
     */
    public static function contains(string $haystack, string|array $needles, bool $ignore_case = false): bool {
        if($ignore_case) {
            $haystack = mb_strtolower($haystack);
            $needles = array_map('mb_strtolower', $needles);
        }

        foreach((array)$needles as $needle) {
            if(str_contains($haystack, $needle) && !empty($needle)) {
                return true;
            }
        }

        return false;
    }
}