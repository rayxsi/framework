<?php

namespace Artificers\Network\Routing;

use Artificers\Network\Http\Request;
use Artificers\Network\Http\Response;
use Artificers\Network\Routing\Controller\Controller;
use Artificers\Supports\Helper\Helper;
use Artificers\Test\PrintTest;

class Router {
    private Request $request;
    private Response $response;
    private static array $routes = [];

    public function __construct(Request $request, Response $response) {
        $this->request = $request;
        $this->response = $response;
    }

    public static function get(string $route, array $handler): void {
        self::$routes['get'][$route] = $handler;
    }

    public static function post(string $route, array $handler): void {
        self::$routes['post'][$route] = $handler;
    }

    public function resolve(): string{
        $uri = $this->request->uri();
        $method = $this->request->method();

        $handler = self::$routes[$method][$uri] ?? false;

        //PrintTest::formattingDump($handler);

        //If route not found
        if($handler === false) {
            Response::_setStatusCode(Response::HTTP_NOT_FOUND);
            return Controller::view();
        }

        //call the handler function from here.....................................
        if(is_array($handler)) {
            $handler[0] = Helper::makeInstance($handler[0]);
        }
        return call_user_func($handler);
    }
}