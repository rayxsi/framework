<?php

namespace Artificers\Support\Concern;

trait Regex {
    protected function match(string $pattern, string $string): array|bool|string {
        return preg_match($pattern, $string);
    }

    protected function matchAll(string $pattern, string $string): array {
        preg_match_all($pattern, $string, $matches);

        return $matches[0];
    }

    protected function replace(string $pattern, string $string, string $input): array|string|null {
        return preg_replace($pattern, $input, $string);
    }
}