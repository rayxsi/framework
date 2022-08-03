<?php
declare(strict_types=1);

use Artificers\Container\Container;
use Artificers\Http\Response;
use Artificers\Supports\Illusion\Env;
use Artificers\Supports\Illusion\RXsiApp;
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

if(!function_exists('full_path')) {
    function full_path(string $path): string {
       return RXsiApp::basePath($path);
    }
}

if(!function_exists('rXsiApp')) {
    function rXsiApp(): Container {
        return Container::makeInstance();
    }
}

