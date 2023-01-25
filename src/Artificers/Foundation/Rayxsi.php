<?php
declare(strict_types=1);
namespace Artificers\Foundation;

use Artificers\Container\Container;
use Artificers\Design\Patterns\Illusion;
use Artificers\Design\Patterns\Pipeline;
use Artificers\Events\EventServiceRegister;
use Artificers\Foundation\Bootstrap\Environment;
use Artificers\Foundation\Bootstrap\LoadConfigFiles;
use Artificers\Foundation\Bootstrap\ServiceRegisters;
use Artificers\Foundation\Config\ErrorHandling;
use Artificers\Foundation\Environment\EnvServiceRegister;
use Artificers\Foundation\Events\BootEvent;
use Artificers\Foundation\Exception\ApplicationFailedException;
use Artificers\Http\Request;
use Artificers\Routing\RouteServiceRegister;
use Artificers\Support\ServiceRegister;
use Artificers\Treaties\Container\BindingException;
use Artificers\Treaties\Container\NotFoundException;
use Artificers\Utility\Ary;
use Closure;
use Exception;


class Rayxsi extends Container {

    /**
     * Base path of rayxsiApp.
     *
     * @var string
     */
    protected string $basePath;

    protected bool $boot = false;

    public static string $minimumPhpVersion = "8.0.0";

    public static string $version = "1.0.0";

    protected array $serviceRegister = [];

    protected array $registeredServices = [];

    protected array $_rayxsiMiddleware = [
        Middleware\VersionChecker::class
    ];

    public function __construct($basePath) {
        defined('DS') or define('DS', '/');
        defined('LOG_DIR') or define('LOG_DIR', $basePath.DS.'tmp'.DS.'logs');

        $this->basePath = $basePath;

        $this->registerNecessaryPath();
        $this->registerContainerAliases();
    }

    /**
     * Run and boot the rayxsi.
     *
     * @throws NotFoundException
     * @throws BindingException
     * @throws Exception
     */
    public function run(string $httpKernel): void {
        //Process the application middleware and core properties that will need for booting.
        $this->boot = (new Pipeline($this))->send($this)
                ->through($this->applicationMiddlewareBasedOnPriority())
                ->next(fn()=>$this->process(function($rayxsi) {
                $rayxsi->registerFoundationBindings();
                $rayxsi->baseServiceRegister();
                $rayxsi->registerBootListeners();
                $this['event.dispatcher']->dispatch(new BootEvent($this));
            }));

        //Now if there was no error of booting then application is working fine. Now it's ready to send back responses.
        $this->booted(function($rayxsi)use($httpKernel) {
            $rXsiAppKernel = $rayxsi->make($httpKernel);
            $response = $rXsiAppKernel->resolve($request = Request::snap())->send();
        });
    }

    /**
     * @param Closure $callback
     * @return bool
     * @throws ApplicationFailedException
     */
    protected function process(Closure $callback): bool {
        try {
            $callback($this);
        }catch(Exception $e) {
            throw new ApplicationFailedException("<b>............Fail to boot rayxsi.......{$e->getMessage()}</b>", $e->getCode());
        }

        return true;
    }

    /**
     * Add application middleware.
     * @param Closure|string $middleware
     * @return void
     */
    public function middleware(Closure|string $middleware): void {
        array_unshift($this->_rayxsiMiddleware, $middleware);
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
     * Return Rayxsi app public dir path.
     *
     * @return string
     */
    public function getPublicPath(): string {
        return $this->basePath.DS.'public';
    }

    /**
     * Return Rayxsi app storage path.
     * @return string
     */
    public function getStoragePath(): string {
        return $this->getBase().DS.'storage';
    }

    /**
     * Return base rayxsiApp path.
     *
     * @return string
     */
    public function getBase(): string {
        return $this->basePath;
    }

    /**
     * @param string $path
     * @return string
     */
    public function basePath(string $path): string {
        return $this->getBase().DIRECTORY_SEPARATOR.$path;
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
        $this->setInstance('path.storage', $this->getStoragePath());
    }

    protected function applicationMiddlewareBasedOnPriority(): array {
        return array_reverse($this->_rayxsiMiddleware);
    }

    /**
     *Set character set.
     *
     * @return void
     * @throws ApplicationFailedException
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

        $this['event.listener']->addEventListener('booting', [
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
            'rXsiApp' => [\Artificers\Foundation\Rayxsi::class, \Artificers\Container\Container::class,
                \Artificers\Treaties\Container\ContainerTreaties::class],
            'env' => [\Artificers\Foundation\Environment\Env::class],
            'error.handle' => [\Artificers\Foundation\Config\ErrorHandling::class],
            'config' => [\Artificers\Config\Repository::class],
            'event' => [\Artificers\Events\Event::class, \Artificers\Treaties\Events\EventTreaties::class],
            'event.dispatcher' => [\Artificers\Events\Dispatcher\EventDispatcher::class, \Artificers\Treaties\Events\EventDispatcherTreaties::class],
            'event.listener' => [\Artificers\Events\Listener\EventListenerProvider::class, \Artificers\Treaties\Events\EventListenerProviderTreaties::class],
            'view' => [\Artificers\View\Generator::class],
            'croxo.engine' => [\Artificers\View\Engines\Croxo::class, \Artificers\Treaties\View\EngineTreaties::class],
            'cache' => [\Artificers\Cache\CacheManager::class],
            'db' => [\Artificers\Database\DatabaseManager::class],
            'db.schema'=>[\Artificers\Database\Lizie\Schema\Schema::class],
            'db.builder'=>[\Artificers\Database\Lizie\Query\Builder::class],
            'dp'=>[\Artificers\Design\DesignPatternFactory::class],
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
        static::setToInstance($this);
        $this->setInstance('rXsiApp', $this);
        $this->setInstance(Container::class, $this);
        Illusion::setIllusionApplication($this);
    }

    protected static function setToInstance(Container|Rayxsi $rXsiApp = null): void {
        static::$instance = $rXsiApp;
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

        //3. call the boot method also
        $identifier->boot();

        //4. now call the register method of service registers to register with rayxsi.
        $identifier->register();

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
        if($this->isBooted()) {
            $callback($this);
        }
    }

    /**
     * check if application is booted.
     * @return bool
     */
    public function isBooted(): bool {
        return $this->boot;
    }
}