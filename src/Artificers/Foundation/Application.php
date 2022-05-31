<?php
namespace Artificers\Foundation;

use Artificers\Filesystem\Filesystem;
use Artificers\Foundation\Config\EnvConfig;
use Artificers\Foundation\Config\Error_Reporting;
use Artificers\Foundation\View\Kernel as ViewKernel;
use Artificers\Network\Http\Request;
use Artificers\Network\Http\Response;
use Artificers\Network\Routing\Router;

class Application {

    //base path or root path
    public string $basePath;
    protected string $tmpDir;
    protected string $nodejs;
    protected string $ssrFile;
    protected string $viewLayout;

    protected Router $router;
    protected EnvConfig $env;
    protected Error_Reporting $errorReporting;

    public ViewKernel $viewKernel;
    public static Application $app;
    public Request $request;
    public Response $response;

    private const _DS_ = Filesystem::_DS_;

    public function __construct($basePath) {
        $this->basePath = $basePath;
        $this->tmpDir = $basePath.self::_DS_."tmp";
        $this->nodejs = "node";
        $this->ssrFile = $basePath.self::_DS_."runtime".self::_DS_."ssr".self::_DS_."server.js";
        $this->viewLayout = $basePath.self::_DS_."view".self::_DS_."main.croxo.php";
        self::$app = $this;

        //First create all necessary instances
        $this->registerInstances();
        $this->_set_configuration();
    }

    private function _set_configuration():void {
        //load Env file
        $this->env->load();

        //set error reporting config
        $this->errorReporting->_set_reporting();

        //load routeService. THIS WILL HAVE TO CHANGE.................................................
        include_once $this->basePath.self::_DS_.'routing'.self::_DS_."web.php";
        include_once $this->basePath.self::_DS_.'routing'.self::_DS_."api.php";
    }

    public function run($request): Application {
       $request::capture();
        $this->getReadyUiEngine();
        return $this;
    }

    public function getTmpDir(): string {
        return $this->tmpDir;
    }

    public function getNodejs(): string {
        return $this->nodejs;
    }

    public function getSsrFile(): string {
        return $this->ssrFile;
    }

    public function getViewLayout(): string {
        return $this->viewLayout;
    }

    public function send(): void {
       echo $this->router->resolve();
    }

    private function registerInstances(): void {
       $this->env = new EnvConfig($this->basePath);
       $this->errorReporting = new Error_Reporting($this->basePath);
       $this->request = new Request();
       $this->response = new Response();
        $this->router = new Router($this->request, $this->response);
        $this->viewKernel = new ViewKernel($this);
    }

    private function getReadyUiEngine(): void {
        $this->viewKernel->loadEngine(Request::$server);
    }

    public static function getApplication(): Application {
        return self::$app;
    }
}