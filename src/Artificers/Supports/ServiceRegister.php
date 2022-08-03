<?php

namespace Artificers\Supports;

use Artificers\Foundation\Rayxsi;

abstract class ServiceRegister {
    protected Rayxsi $rXsiApp;

    public function __construct(Rayxsi $rXsiApp) {
        $this->rXsiApp = $rXsiApp;
    }

    /**
    *Register services with Rayxsi.
     *
     * This method could be overwritten.
     *
     * @return void
     */
    public function register(): void{
        //Register code goes here
    }

    public function boot(): void {}
}