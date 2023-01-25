<?php

namespace Artificers\Foundation\Events;

use Artificers\Events\Event;
use Artificers\Foundation\Rayxsi;

class PreBootEvent extends Event {
    public const type = 'boot_processing';
    protected Rayxsi $rayxsi;

    public function __construct(Rayxsi $rayxsi) {
        $this->rayxsi = $rayxsi;
    }
}