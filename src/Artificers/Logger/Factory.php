<?php
declare(strict_types=1);
namespace Artificers\Logger;

use Artificers\Foundation\Rayxsi;
use InvalidArgumentException;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger as MonoLogger;

/**
 * @author Topu <toerso.mechanix@gmail.com>
 */
class Factory {
    protected const MAP_DRIVER_TO_HANDLER = [
        'stream'=>StreamHandler::class,
        'regular'=>RotatingFileHandler::class,
        'sis'=>SyslogHandler::class,
        'error'=>ErrorLogHandler::class
    ];

    private Rayxsi $rayxsi;

    public function __construct(Rayxsi $rayxsi) {
        $this->rayxsi = $rayxsi;
    }

    protected function make(array $config) {
        $method = $config['driver']."handler";

        if(isset(self::MAP_DRIVER_TO_HANDLER[$config['driver']])) {
            return $this->$method($config);
        }
    }

    protected function streamHandler(array $config): StreamHandler {
        return new StreamHandler($config['path'], $config['level'],
            $config['bubble'] ?? true, $config['permission'] ?? null, $config['locking'] ?? false);
    }

    protected function regularHandler(array $config): RotatingFileHandler {
        return new RotatingFileHandler($config['path'], $config['max_file_in_a_day'] ?? 10, $config['level'],
            $config['bubble'] ?? true, $config['permission'] ?? null, $config['locking'] ?? false);
    }

    protected function sisHandler(array $config): SyslogHandler {
        return new SyslogHandler($this->rayxsi['config']->get('rXsiApp.name'), $config['facility'] ?? LOG_USER, $config['level']);
    }

    protected function errorHandler(array $config): ErrorLogHandler {
        return new ErrorLogHandler( $config['type'] ?? ErrorLogHandler::OPERATING_SYSTEM, $config['level']);
    }

    public function createStackLogger(array $params): MonoLogger {
        $channels = array_reverse($params['channels']);
        $handlers = [];

        foreach($channels as $channel) {
            $config = $this->rayxsi['config']->get("logger.channels.{$channel}");
            $handlers[] = $this->make($config);
        }

        return new MonoLogger('stack', array_reverse($handlers));
    }

    public function createStreamLogger(array $config): MonoLogger {
        return new MonoLogger($config['driver'], $this->prepareHandlers($this->streamHandler($config)));
    }

    public function createRegularLogger(array $config): MonoLogger {
        return new MonoLogger($config['driver'], $this->prepareHandlers($this->regularHandler($config)));
    }

    public function createSystemLogger(array $config): MonoLogger {
        return new MonoLogger($config['driver'], $this->prepareHandlers($this->sisHandler($config)));
    }

    public function createErrorLogger(array $config): MonoLogger {
        return new MonoLogger($config['driver'], $this->prepareHandlers($this->errorHandler($config)));
    }

    public function createMonolog(array $config): MonoLogger {
        if (! is_a($config['handler'], HandlerInterface::class, true)) {
            throw new InvalidArgumentException(
                $config['handler'].' must be an instance of '.HandlerInterface::class
            );
        }

        $with = array_merge(
            ['level' => $config['level'] ?? env('log_level')],
            $config['with'] ?? [],
            $config['handler_with'] ?? []
        );

        return new MonoLogger($config['driver'], $this->prepareHandlers($this->rayxsi->make($config['handler'], $with)));
    }

    protected function prepareHandlers($handler): array {
        return func_get_args();
    }
}