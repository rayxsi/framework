<?php

use Artificers\Http\Response;
use Artificers\Supports\Illusion\Env;
use Artificers\Supports\Illusion\View;

if(!function_exists('env')) {
    function env($key, $default=null): mixed {
        return Env::collect($key) ?? $default;
    }
}

if(!function_exists('dodo')) {
    function dodo($value): void {
        echo '<pre>';
        var_dump($value);
        echo '</pre>';
    }
}

if(!function_exists('response')) {
    function response(string $content = "", int $status = 200, array $headers = []): Response {
        return new Response($content, $status, $headers);
    }
}

