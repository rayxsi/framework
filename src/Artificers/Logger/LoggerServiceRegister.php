<?php
declare(strict_types=1);
namespace Artificers\Logger;

use Artificers\Foundation\Rayxsi;
use Artificers\Support\ServiceRegister;

/**
 * @author Topu <toerso.mechanix@gmail.com>
 */
class LoggerServiceRegister extends ServiceRegister {
    public function register(): void {
        $this->rXsiApp->singleton('logger', function(Rayxsi $rayxsi) {
            $manager = new LogManager($rayxsi);
            $driver = $rayxsi['configuration']->get('logger.default');
            $config = $rayxsi['configuration']->get("logger.channels.{$driver}");
            return $manager->make($config);
        });
    }
}