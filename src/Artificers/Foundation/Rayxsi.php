<?php
declare(strict_types=1);

namespace Artificers\Foundation;

use Artificers\Container\Container;
use Artificers\Events\EventServiceRegister;
use Artificers\Foundation\Config\Config;
use Artificers\Foundation\Config\ErrorHandling;
use Artificers\Foundation\Environment\Env;
use Artificers\Foundation\Environment\EnvServiceRegister;
use Artificers\Treaties\Container\BindingException;
use Artificers\Treaties\Container\NotFoundException;
use Artificers\Routing\RouteServiceRegister;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;


class Rayxsi extends Container {

    //base path or root path
    protected string $basePath;

    public function __construct($basePath) {
        defined('DS') or define('DS', '/');
        defined('LOG_DIR') or define('LOG_DIR', $basePath.DS.'tmp'.DS.'logs');
        $this->basePath = $basePath;
    }

    /**
     * @throws NotFoundException
     * @throws BindingException
     */
    public function run(string $httpKernel) {

        if(version_compare($phpVersion = PHP_VERSION, $mechanixMinVersion = Config::MECHANIX_MIN_REQUIRED_VERSION, '<')) {
            die(sprintf('You are currently running PHP version[%s], but the Mechanix framework requires at least PHP version [%s]', $phpVersion, $mechanixMinVersion));
        }

        $this->registerNecessaryPath();
        $this->registerContainerAliases();
        $this->registerFoundationBindings();
        $this->serviceRegister();
        $this->environment();
        $this->errorHandler();

        return $this->make($httpKernel);
    }

    public function getAppPath(): string {
        return $this->basePath.DS.'app';
    }

    public function getConfigPath(): string {
        return $this->basePath.DS.'config';
    }

    public function getDatabasePath(): string {
        return $this->basePath.DS.'database';
    }

    public function getSsrPath(): string {
        return $this->basePath.DS.'runtime'.DS.'ssr';
    }

    public function getTmpPath(): string {
        return $this->basePath.DS.'tmp';
    }

    public function getViewPath(): string {
        return $this->basePath.DS.'view';
    }

    public function getPublicPath(): string {
        return $this->basePath.DS.'public';
    }

    public function getBase(): string {
        return $this->basePath;
    }

    public function send(): void {
        $this['router'];
    }

    private function registerNecessaryPath(): void {
        $this->setInstance('path.app', $this->getAppPath());
        $this->setInstance('path.base', $this->getBase());
        $this->setInstance('path.config', $this->getConfigPath());
        $this->setInstance('path.tmp', $this->getTmpPath());
        $this->setInstance('path.ssr', $this->getSsrPath());
        $this->setInstance('path.database', $this->getDatabasePath());
        $this->setInstance('path.view', $this->getViewPath());
        $this->setInstance('path.public', $this->getPublicPath());
    }

    /**
     *Set character set and load .env file.
     *
     * @return void
     */
    private function environment(): void {
        ini_set('default_charset', 'UTF-8');
        $this['env']->load();
    }

    private function errorHandler(): void {
        error_reporting(E_ALL | E_STRICT);
        set_error_handler([ErrorHandling::class, 'errorHandling']);
        set_exception_handler([ErrorHandling::class, 'exceptionHandling']);
    }

    private function registerContainerAliases() {
        $mechanix = [
            'rXsiApp' => [\Artificers\Foundation\Rayxsi::class, \Artificers\Container\Container::class, \Artificers\Treaties\Container\ContainerTreaties::class, \Artificers\Foundation\Config\Config::class],
            'env' => [Environment\Env::class],
            'error.handle' => [\Artificers\Foundation\Config\ErrorHandling::class],
            'event' => [\Artificers\Events\Event::class, \Artificers\Treaties\Events\EventTreaties::class],
            'event.dispatcher' => [\Artificers\Events\Dispatcher\EventDispatcher::class, \Artificers\Treaties\Events\EventDispatcherTreaties::class],
            'event.listener' => [\Artificers\Events\Listener\EventListenerProvider::class, \Artificers\Treaties\Events\EventListenerProviderTreaties::class],
            'view' => [\Artificers\Foundation\View\Kernel::class],
            'croxo.engine' => [\Artificers\View\Engines\Croxo::class, \Artificers\Treaties\View\Engine::class],
            'router' => [\Artificers\Network\Routing\Router::class],
            'request' => [\Artificers\Network\Http\Request::class, \Symfony\Component\HttpFoundation\Request::class],
            'response' => [\Artificers\Network\Http\Response::class, \Symfony\Component\HttpFoundation\Response::class]
        ];

        foreach($mechanix as $key=>$aliases) {
            foreach($aliases as $alias) {
                $this->setAlias($key, $alias);
            }
        }
    }

    private function registerFoundationBindings(): void {
        $this->setInstance('rXsiApp', $this);
        $this->setInstance(Container::class, $this);
    }

    private function serviceRegister() {
        $this->register(new EnvServiceRegister($this));
        $this->register(new EventServiceRegister($this));
        $this->register(new RouteServiceRegister($this));
    }

    private function register(object $identifier) {
        $identifier->register();
    }
}