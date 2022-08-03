<?php
declare(strict_types=1);
namespace Artificers\Foundation\Bootstrap;

use Artificers\Foundation\Rayxsi;
use Artificers\Treaties\Bootstrap\BootstrapListenerTreaties;

class ServiceRegisters implements BootstrapListenerTreaties {
    public function load($event): void {
        $event->rXsiApp->registerConfiguredServices();
    }
}