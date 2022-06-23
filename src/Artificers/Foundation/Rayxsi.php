<?php
declare(strict_types=1);

namespace Artificers\Foundation;

use Artificers\Cache\CacheServiceRegister;
use Artificers\Container\Container;
use Artificers\Events\EventServiceRegister;
use Artificers\Foundation\Bootstrap\Environment;
use Artificers\Foundation\Bootstrap\LoadConfigFiles;
use Artificers\Foundation\Bootstrap\ServiceRegisters;
use Artificers\Foundation\Config\Config;
use Artificers\Foundation\Config\ErrorHandling;
use Artificers\Foundation\Environment\Env;
use Artificers\Foundation\Environment\EnvServiceRegister;
use Artificers\Foundation\Events\BootEvent;
use Artificers\Routing\RouteServiceRegister;
use Artificers\Supports\Illusion\Illusion;
use Artificers\Supports\ServiceRegister;
use Artificers\Treaties\Container\BindingException;
use Artificers\Treaties\Container\NotFoundException;

use Artificers\Utilities\Ary;
use Artificers\View\ViewServiceRegister;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;


class Rayxsi extends Container {

    /**
     * Base path of rayxsiApp.
     *
     * @var string
     */
    protected string $basePath;

    protected bool $boot = false;

    protected array $serviceRegister = [];

    protected array $registeredServices = [];

    public function __construct($basePath) {
        defined('DS') or define('DS', '/');
        defined('LOG_DIR') or define('LOG_DIR', $basePath.DS.'tmp'.DS.'logs');

        $this->basePath = $basePath;
    }

    /**
     * Run and boot the rayxsi.
     *
     * @throws NotFoundException
     * @throws BindingException
     */
    public function run(string $httpKernel) {
        if(version_compare($phpVersion = PHP_VERSION, $mechanixMinVersion = Config::MECHANIX_MIN_REQUIRED_VERSION, '<')) {
            die(sprintf('You are currently running PHP version[%s], but the Mechanix framework requires at least PHP version [%s]', $phpVersion, $mechanixMinVersion));
        }

        //If all fine then boot
        $this->boot = true;

        $this->registerNecessaryPath();
        $this->registerContainerAliases();
        $this->registerFoundationBindings();
        $this->baseServiceRegister();
        $this->registerBootListeners();

        $this['event.dispatcher']->dispatch(new BootEvent($this));

        return $this->make($httpKernel);
    }

    /**
     * Return rayxsiApp app dir path.
     *
     * @return string
     */
    public function getAppPath(): string {
        return $this->basePath.DS.'app';
    }

    /**
     * Return rayxsiApp config dir path.
     *
     * @return string
     */
    public function getConfigPath(): string {
        return $this->basePath.DS.'config';
    }

    /**
     * Return rayxsiApp database dir path.
     *
     * @return string
     */
    public function getDatabasePath(): string {
        return $this->basePath.DS.'database';
    }

    /**
     * Return rayxsiApp ssr path.
     *
     * @return string
     */
    public function getSsrPath(): string {
        return $this->basePath.DS.'runtime'.DS.'ssr';
    }

    /**
     * Return rayxsiApp tmp dir path.
     *
     * @return string
     */
    public function getTmpPath(): string {
        return $this->basePath.DS.'tmp';
    }

    /**
     * Return rayxsiApp view dir path.
     *
     * @return string
     */
    public function getViewPath(): string {
        return $this->basePath.DS.'view';
    }

    /**
     * Return rayxsiApp public dir path.
     *
     * @return string
     */
    public function getPublicPath(): string {
        return $this->basePath.DS.'public';
    }

    /**
     * Return base rayxsiApp path.
     *
     * @return string
     */
    public function getBase(): string {
        return $this->basePath;
    }

    public function send(): void {
        $this['router'];
    }

    /**
     * Register all necessary path of rayxsiApp.
     *
     * @return void
     */
    private function registerNecessaryPath(): void {
        $this->setInstance('path.app', $this->getAppPath());
        $this->setInstance('path.base', $this->getBase());
        $this->setInstance('path.config', $this->getConfigPath());
        $this->setInstance('path.tmp', $this->getTmpPath());
        $this->setInstance('path.ssr', $this->getSsrPath());
        $this->setInstance('path.node', 'node');
        $this->setInstance('path.database', $this->getDatabasePath());
        $this->setInstance('path.view', $this->getViewPath());
        $this->setInstance('path.public', $this->getPublicPath());
    }

    /**
     *Set character set.
     *
     * @return void
     */
    public function environment(): void {
        ini_set('default_charset', 'UTF-8');
        $this['env']->load();
    }

    /**
     * Generate all error into exceptions.
     *
     * @return void
     */
    public function errorHandler(): void {
        error_reporting(E_ALL | E_STRICT);
        set_error_handler([ErrorHandling::class, 'errorHandling']);
        set_exception_handler([ErrorHandling::class, 'exceptionHandling']);
    }

    /**
     * Register boot listeners.
     *
     * @return void
     */
    protected function registerBootListeners(): void {

        $this['event.listener']->addEventListener('boot', [
            Environment::class.'@load',
            LoadConfigFiles::class.'@load',
            ServiceRegisters::class.'@load'
        ]);
    }

    /**
     * @throws NotFoundException
     * @throws BindingException
     */
    public function registerConfiguredServices(): void {
        //resolve all registers from repository
       $registers = $this['config']->get('rXsiApp.registers');

       //make instance of all service register
       foreach($registers as $idx => $register) {
           $this->register($register);

           unset($registers[$idx]);
       }


    }

    /**
     * Register container aliases.
     *
     * @return void
     */
    private function registerContainerAliases(): void {
        $mechanix = [
            'rXsiApp' => [\Artificers\Foundation\Rayxsi::class, \Artificers\Container\Container::class, \Artificers\Treaties\Container\ContainerTreaties::class, \Artificers\Foundation\Config\Config::class],
            'env' => [\Artificers\Foundation\Environment\Env::class],
            'error.handle' => [\Artificers\Foundation\Config\ErrorHandling::class],
            'config' => [\Artificers\Config\Repository::class],
            'event' => [\Artificers\Events\Event::class, \Artificers\Treaties\Events\EventTreaties::class],
            'event.dispatcher' => [\Artificers\Events\Dispatcher\EventDispatcher::class, \Artificers\Treaties\Events\EventDispatcherTreaties::class],
            'event.listener' => [\Artificers\Events\Listener\EventListenerProvider::class, \Artificers\Treaties\Events\EventListenerProviderTreaties::class],
            'view' => [\Artificers\View\Generator::class],
            'croxo.engine' => [\Artificers\View\Engines\Croxo::class, \Artificers\Treaties\View\EngineTreaties::class],
            'router' => [\Artificers\Routing\Router::class],
            'request' => [\Artificers\Http\Request::class, \Symfony\Component\HttpFoundation\Request::class],
            'response' => [\Artificers\Http\Response::class, \Symfony\Component\HttpFoundation\Response::class]
        ];

        foreach($mechanix as $key=>$aliases) {
            foreach($aliases as $alias) {
                $this->setAlias($key, $alias);
            }
        }
    }

    /**
     * Register base bindings.
     *
     * @return void
     */
    private function registerFoundationBindings(): void {
        $this->setInstance('rXsiApp', $this);
        $this->setInstance(Container::class, $this);
        Illusion::setIllusionApplication($this);
    }

    /**
     * Base service Register.
     *
     * @return void
     */
    private function baseServiceRegister(): void {
        $this->register(new EnvServiceRegister($this));
        $this->register(new EventServiceRegister($this));
        $this->register(new RouteServiceRegister($this));
    }

    /**
     * Call register method of service register.
     *
     * @param object|string $identifier
     * @param bool $force
     * @return ?ServiceRegister
     */
    public function register(object|string $identifier, bool $force=false): ?ServiceRegister {
        //1. we have to check if service register already applied. If it is then just return it.

        if(($registered = $this->getServiceRegister($identifier)) && !$force) {
            return $registered;
        }

        //2. if service register passed by string then we have to resolve it.

        if(is_string($identifier)) {
            $identifier = $this->resolveServiceRegister($identifier);
        }

        //3. now call the register method of service registers to register with rayxsi.
        $identifier->register();

        //4. call the boot method also
        if($this->isBooted()) {
            $identifier->boot();
        }

        //5. at last set mark to this service register that is applied.
        $this->markAsRegistered($identifier);

        return $identifier;
    }

    /**
     * @param object|string $register
     * @return mixed
     */
    protected function getServiceRegister(object|string $register): mixed {
        $id = is_string($register) ? $register : get_class($register);

        $registers = Ary::filter($this->serviceRegister, function($value) use($id){
            return $value instanceof $id;
        }, 'both');

        return array_values($registers)[0] ?? null;
    }

    /**
     * @param string $register
     * @return ServiceRegister
     */
    protected function resolveServiceRegister(string $register): ServiceRegister {
        return $this[$register];
    }

    protected function markAsRegistered(object $name) {
        $this->serviceRegister[] = $name;
        $this->registeredServices[get_class($name)] = true;
    }

    public function booted($callback) {
        if($this->boot) {
            $callback($this);
        }
    }

    /**
     * @return bool
     */
    public function isBooted(): bool {
        return $this->boot;
    }
}