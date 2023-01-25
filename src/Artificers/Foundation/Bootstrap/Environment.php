<?php
declare(strict_types=1);
namespace Artificers\Foundation\Bootstrap;

use Artificers\Treaties\Bootstrap\BootstrapListenerTreaties;

class Environment implements BootstrapListenerTreaties {
    public function load($event): void {
        $event->getRayxsi()->environment();
        $event->getRayxsi()->errorHandler();
    }
}