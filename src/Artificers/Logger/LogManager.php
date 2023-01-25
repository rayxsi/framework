<?php declare(strict_types=1);
namespace Artificers\Logger;

use Artificers\Foundation\Rayxsi;
use Monolog\Logger;

/**
 * LogManager class holds all the possible logger object.
 *
 *
 * @author Topu <toerso.mechanix@gmail.com>
 */
class LogManager {
    protected const MAP_TO_METHOD = [
        'stack'=>'createStackLogger',
        'stream'=>'createStreamLogger',
        'regular'=>'createRegularLogger',
        'sis'=>'createSystemLogger',
        'error'=>'createErrorLogger',
        'monolog'=>'createMonolog'
    ];

    protected Rayxsi $rayxsi;

    public function __construct(Rayxsi $rayxsi) {
        $this->rayxsi = $rayxsi;
    }

    public function make(array $params): Logger|bool {
        $method = self::MAP_TO_METHOD[$params['driver']] ?? false;

        if(!$method) return false;

        return (new Factory($this->rayxsi))->$method($params);
    }

    public function channel(string $channel): Logger|bool {
        if($channel === 'deprecations') {
            $config = $this->rayxsi['config']->get("logger.{$channel}");
            $config = $this->rayxsi['config']->get("logger.channels.{$config['channel']}");
        }else {
            $config = $this->rayxsi['config']->get("logger.channel.{$channel}");
        }

        return $this->make($config);
    }
}