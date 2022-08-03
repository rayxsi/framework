<?php
declare(strict_types=1);

namespace Artificers\Foundation\Http;

use Artificers\Foundation\Rayxsi;
use Artificers\Http\Request;
use Artificers\Http\Response;
use Artificers\Routing\Router;
use Artificers\Treaties\Http\HttpKernelTreaties;
use Artificers\View\ViewServiceRegister;

class Kernel implements HttpKernelTreaties {
    protected Rayxsi $rXsiApp;
    protected Router $router;

    public function __construct(Rayxsi $rXsiApp, Router $router) {
        $this->rXsiApp = $rXsiApp;
        $this->router = $router;
    }

    public function resolve(Request $request): Response {
        //set the current request to the container so that we can use it until the response back.
        $this->rXsiApp->setInstance('request', $request);

        //here we need to explicitly bind the server info to the front end.
        $this->rXsiApp['view']->compiler->bindServer($this->rXsiApp['request']->getSerializedServerInfo());

        return $this->router->resolveWithRouter($request);
    }
}