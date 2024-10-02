<?php
declare(strict_types=1);
namespace Artificers\Foundation\Http;

use Artificers\Foundation\Rayxsi;
use Artificers\Http\Request;
use Artificers\Routing\Router;
use Artificers\Support\Illusion\Route;
use Artificers\Treaties\Exception\ExceptionHandler;
use Artificers\Treaties\Http\HttpKernelTreaties;
use Closure;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Kernel implements HttpKernelTreaties {
    protected Rayxsi $rXsiApp;
    protected Router $router;

    public function __construct(Rayxsi $rXsiApp, Router $router) {
        $this->rXsiApp = $rXsiApp;
        $this->router = $router;
    }

    public function resolve(Request $request): Response {
        try {
            $this->router->refreshCollection();
            $response = $this->pushRequestThroughRouter($request);
        }catch(Throwable $e) {
            $response = $this->renderException($request, $e);
        }

        return $response;
    }

    protected function pushRequestThroughRouter(Request $request): Response {
        //Set the current request to the container so that we can use it until the response is going back.
        $this->rXsiApp->setInstance('request', $request);
        Route::removeCachedInstance('request');

        return $this->rXsiApp['dp']->get('Pipeline')->send($request)
            ->through($this->resolveWithGlobalMiddleware())
            ->next($this->dispatchToRouter());
    }

    protected function dispatchToRouter(): Closure{
        return function($request) {
            //Here we need to explicitly bind the server info to the front-end engine.
            $this->rXsiApp['view']->compiler->bindServer($request->getSerializedServerInfo());

            return $this->router->resolve($request);
        };
    }

    protected function resolveWithGlobalMiddleware(): array {
        return array_reverse($this->rXsiApp['middleware']->get('global'));
    }

    protected function renderException(Request $request, Throwable $e): Response {
        return $this->rXsiApp->make(ExceptionHandler::class)->render($request, $e);
    }
}