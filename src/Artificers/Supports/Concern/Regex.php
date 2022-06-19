<?php

namespace Artificers\Supports\Concern;

trait Regex {
    protected function match(string $pattern, string $string): array|bool|string {
        return preg_match($pattern, $string);
    }

    protected function matchAll(string $pattern, string $string): array {
        preg_match_all($pattern, $string, $matches);

        return $matches[0];
    }
}