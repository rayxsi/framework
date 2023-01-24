<?php
declare(strict_types=1);
namespace Artificers\Design\Patterns;

use Artificers\Container\Container;
use Artificers\Design\Treaties\Pattern;
use Artificers\Treaties\Container\BindingException;
use Artificers\Treaties\Container\NotFoundException;
use BadMethodCallException;
use Closure;
use Exception;

class Pipeline implements Pattern {
    protected mixed $argument;

    protected array $pipesStack;

    /**
     * Method that will be called.
     * @var string
     */
    protected string $method = "__invoke";

    protected Closure $start;

    private Container $container;

    public function __construct(Container $container = null) {
        $this->container = $container ?? new Container();

        $this->start = function() {
            //
        };
    }

    /**
     * Send a variable through all the pipes.
     *
     * @param mixed $arg
     * @return $this
     */
    public function send(mixed $arg): static {
        $this->argument = $arg;

        return $this;
    }

    /**
     * Take pipes and process them.
     *
     * Pipe can be any object or closure that you want to run sequentially.
     *
     * N.B: Pipe class must have __invoke($arg, $next) magic method implemented. And return $next($arg).
     *
     * @param mixed $pipes
     * @return $this
     */
    public function through(mixed $pipes): static {
        $this->pipesStack = is_array($pipes) ? $pipes : func_get_args();

        return $this;
    }

    /**
     * Takes a pipe and process it.
     *
     * Pipe can be any object or closure that you want to run sequentially.
     *
     * N.B: Pipe class must have __invoke($arg, $next) magic method implemented. And return $next($arg).
     *
     * @param mixed $pipe
     * @return $this
     */
    public function pipe(mixed $pipe): static {
        $next = $this->start;
        $this->start = function($arg) use($pipe, $next) {
           if(is_callable($pipe)) {
               return $pipe($arg, $next);
           }

           $pipe = $this->checkObjAndResolve($pipe);

            if(method_exists($pipe, $this->method)) {
                return $pipe($arg, $next);
            }
            $this->generateException(get_class($pipe));
        };

        return $this;
    }

    /**
     * Resolve pipe with container.
     *
     * @param $pipe
     * @return mixed
     * @throws BindingException
     * @throws NotFoundException
     */
    private function resolveWithContainer($pipe): mixed {
        return $this->container->make($pipe);
    }

    /**
     * It is used with pipe method. Run all the pipes and return the variable that was sent.
     * @return mixed
     */
    public function run(): mixed {
        return call_user_func($this->start, $this->argument);
    }

    /**
     * Check if pipe is object. If not then resolve with container and return.
     * @param mixed $pipe
     * @return mixed
     */
    protected function checkObjAndResolve(mixed $pipe): mixed {
        if(is_object($pipe)){
            return $pipe;
        }

        return $this->resolveWithContainer($pipe);
    }

    /**
     * Return the argument that was sent through.
     * @return mixed
     */
    public function thenResolve(): mixed {
        return $this->next(function($arg) {
            return $arg;
        });
    }

    /**
     * Execute last piece of code.
     * @param Closure $next
     * @return mixed
     */
    public function next(Closure $next): mixed {
        $pipeline = array_reduce(array_reverse($this->pipesStack), $this->processOnion(), $this->processFinalDestination($next));
        return $pipeline($this->argument);
    }

    /**
     * Returns a closure that will wrap the final destination.
     * @param Closure $next
     * @return Closure
     */
    protected function processFinalDestination(Closure $next): Closure {
        return function($arg) use($next) {
            return $next($arg);
        };
    }

    /**
     * Generate the onion.
     * @return Closure
     */
    protected function processOnion(): Closure {
        return function($next, $pipe) {
            return function($arg) use($next, $pipe) {
                if(is_callable($pipe)) {
                    return $pipe($arg, $next);
                }

                if(!is_object($pipe)) {
                    $pipe = $this->resolveWithContainer($pipe);
                }

                if(method_exists($pipe, $this->method)) {
                    return $pipe($arg, $next);
                }
                $this->generateException(get_class($pipe));
            };
        };
    }

    /**
     * Generate the bad method call exception.
     * @param mixed $something
     * @return void
     */
    private function generateException(mixed $something): void {
        throw new BadMethodCallException("{$this->method} method not found in class {$something}");
    }
}