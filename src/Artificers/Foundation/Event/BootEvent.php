<?php

namespace Artificers\Foundation\Event;

use Artificers\Event\Event;
use Artificers\Foundation\Rayxsi;

class BootEvent extends Event {
    public const type = 'boot';
    public Rayxsi $rXsiApp;

    public function __construct(Rayxsi $rXsiApp) {
        $this->rXsiApp = $rXsiApp;
    }
}