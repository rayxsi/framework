<?php
declare(strict_types=1);
namespace Artificers\Design;

use Artificers\Support\ServiceRegister;

class DesignPatternServiceRegister extends ServiceRegister {
    public function register(): void {
        $this->rXsiApp->singleton('dp', function($rXsiApp) {
            return new DesignPatternFactory($rXsiApp);
        });
    }
}