<?php declare(strict_types=1);
namespace Artificers\Foundation\Bootstrap;


use Artificers\Foundation\Rayxsi;
use Artificers\Logger\LogManager;
use Artificers\Treaties\Bootstrap\BootstrapListenerTreaties;
use Artificers\Treaties\Container\BindingException;
use Artificers\Treaties\Exception\ExceptionHandler;
use Closure;
use ErrorException;
use Symfony\Component\ErrorHandler\Error\FatalError;
use Throwable;

/**
 * ExceptionHandle class works with all errors and converts all errors into exceptions and response back.
 *
 *
 * @author Topu <toerso.mechanix@gmail.com>
 */
class ExceptionHandle implements BootstrapListenerTreaties {
    private static Rayxsi $rXsiApp;

    public function load($event): void {
        static::$rXsiApp = $event->getRayxsi();

        error_reporting(-1); //reporting all
        set_error_handler($this->parseTo('__handleError'));
        set_exception_handler($this->parseTo('__handleException'));
        register_shutdown_function($this->parseTo('__handleShutdownEvent'));

        if(!static::$rXsiApp['env']->collect('app_debug_mode')) {
            ini_set('display_errors', 0);
        }
    }

    /**
     * @param int       $errorLevel     Error level.
     * @param string    $message        Error message.
     * @param string    $file           File where error was occurred.
     * @param int       $line           Which line the error was occurred.
     * @throws ErrorException           Throws ErrorException.
     */
    public function __handleError(int $errorLevel, string $message, string $file = '', int $line = 0): void {
        //handle deprecated errors
        if($this->isDeprecated($errorLevel)) {
            $this->handleDeprecation($message, $file, $line, $errorLevel);
        }

        //checking bit by bit
        if(error_reporting() & $errorLevel) {
            throw new ErrorException($message, 0, $errorLevel, $file, $line);
        }
    }


    public function __handleException(Throwable $e): void {

        try {
            $this->generateExceptionHandler()->report($e);
        }catch (BindingException $e) {
            //..
            $failedToResolveExceptionHandler = true;
        }
        if($failedToResolveExceptionHandler ?? false) exit(1);
        else $this->renderWithHttpResponse($e);
    }

    private function renderWithHttpResponse(throwable $e): void {
        $this->generateExceptionHandler()->throw(static::$rXsiApp['request'], $e)->send();
    }

    public function __handleShutdownEvent(): void{
        if (!is_null($error = error_get_last()) && $this->isFatal($error['type'])) {
            $this->__handleException($this->handleFatalError($error, 0));
        }
    }

    protected function handleDeprecation(string $message, string $file, int $line, int $errorLevel = E_DEPRECATED): void {
        if(!class_exists(LogManager::class)) {
            return;
        }

        try {
            $logger = $this->generateLogger();
        }catch (BindingException $e) {
            return;
        }
        $options = static::$rXsiApp['configuration']->get('logger.deprecations');

        invokeWith($logger->channel('deprecations'), function($log) use($message, $file, $line, $errorLevel, $options) {
            if(isset($options['trace']) && $options['trace']) {
                $log->warning(new ErrorException($message, 0, $errorLevel, $file, $line));
            }else {
                $log->warning(sprintf('%s in %s on line %s',
                    $message, $file, $line
                ));
            }
        });
    }

    protected function handleFatalError(array $error, ?int $traceOfSet = null): FatalError {
        return new FatalError($error['message'], 0, $error, $traceOfSet);
    }

    protected function isDeprecated(int $level): bool {
        return in_array($level, [E_DEPRECATED, E_USER_DEPRECATED]);
    }

    protected function isFatal(int $level): bool {
        return in_array($level,  [E_COMPILE_ERROR, E_CORE_ERROR, E_PARSE, E_ERROR]);
    }

    /**
     * @throws BindingException
     */
    protected function generateLogger(): LogManager {
        return static::$rXsiApp->make(LogManager::class);
    }

    /**
     * @throws BindingException
     */
    protected function generateExceptionHandler(): ExceptionHandler {
        return static::$rXsiApp->make(ExceptionHandler::class);
    }

    protected function parseTo(string $method): Closure {
        return fn(...$args)=>static::$rXsiApp ? $this->{$method}(...$args) : false;
    }
}