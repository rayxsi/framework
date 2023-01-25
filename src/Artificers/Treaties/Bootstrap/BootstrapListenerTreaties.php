<?php
declare(strict_types=1);
namespace Artificers\Treaties\Bootstrap;

interface BootstrapListenerTreaties {
    public function load($event): void;
}