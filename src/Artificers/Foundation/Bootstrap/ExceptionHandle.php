<?php declare(strict_types=1);
namespace Artificers\Foundation\Bootstrap;

use Artificers\Foundation\Config\Exception\ErrorHandler;
use Artificers\Foundation\Rayxsi;
use Artificers\Logger\LogManager;
use Artificers\Treaties\Bootstrap\BootstrapListenerTreaties;
use Artificers\Treaties\Container\BindingException;
use Artificers\Treaties\Container\NotFoundException;
use Closure;
use ErrorException;
use Throwable;

/**
 * ExceptionHandle class works with all errors and converts all errors into exceptions and response back.
 *
 *
 * @author Topu <toerso.mechanix@gmail.com>
 */
class ExceptionHandle implements BootstrapListenerTreaties {
    private Rayxsi $rayxsi;

    public function load($event): void {
        $this->rayxsi = $event->getRaxsi();

        error_reporting(-1); //reporting all
        set_error_handler($this->parseTo('handleError'));
        set_exception_handler($this->parseTo('handleException'));
        register_shutdown_function($this->parseTo('handleShutdownEvent'));

        if(!$this->rayxsi['env']->collect('app_debug_mode')) {
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
    public function handleError(int $errorLevel, string $message, string $file = '', int $line = 0) {
        //handle deprecated errors
        if($this->isDeprecated($errorLevel)) {
            $this->handleDeprecation($message, $file, $line, $errorLevel);
        }

        //checking bit by bit
        if(error_reporting() & $errorLevel) {
            throw new ErrorException($message, 0, $errorLevel, $file, $line);
        }
    }

    public function handleException(Throwable $exp) {
        $this->getExceptionHandler()->throw($this->rayxsi['request'], $exp)->send();
    }

    protected function handleDeprecation(string $message, string $file, int $line, int $errorLevel = E_DEPRECATED) {
        if(!class_exists(LogManager::class)) {
            return;
        }

        try {
            $logger = $this->generateLogger();
        }catch (BindingException|NotFoundException $e) {
            return;
        }
        $options = $this->rayxsi['config']->get('logger.deprecations');

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

    protected function isDeprecated(int $level): bool {
        return in_array($level, [E_DEPRECATED, E_USER_DEPRECATED]);
    }

    /**
     * @throws NotFoundException
     * @throws BindingException
     */
    protected function generateLogger(): LogManager {
        return $this->rayxsi->make(LogManager::class);
    }

    protected function getExceptionHandler(): ErrorHandler {
        return new ErrorHandler();
    }

    protected function parseTo(string $method): Closure {
        return fn(...$args)=>$this->rayxsi ? $this->{$method}(...$args) : false;
    }
}