<?php
declare(strict_types=1);

use Artificers\Container\Container;
use Artificers\Http\Response;
use Artificers\Support\Illusion\Env;
use Artificers\Support\Illusion\RXsiApp;
use Artificers\Support\Illusion\View;

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

if(!function_exists("auto")) {

    /**
     * Call a callback and returns its value.
     *
     * @param mixed $value
     * @param Closure|null $callback
     * @return mixed
     */
    function auto(mixed $value, Closure|null $callback=null): mixed {
        $callback($value);

        return $value;
    }
}

if(!function_exists('view')) {

    /**
     * Generate view.
     * @return \Artificers\View\View
     */
    function view(): \Artificers\View\View{
        return View::generate();
    }
}

