<?php declare(strict_types=1);
namespace Artificers\Foundation\Bootstrap;

use Artificers\Treaties\Bootstrap\BootstrapListenerTreaties;

class Environment implements BootstrapListenerTreaties {
    public function load($event): void {
        ini_set('default_charset', 'UTF-8');
        $event->getRayxsi()->environment();
    }
}