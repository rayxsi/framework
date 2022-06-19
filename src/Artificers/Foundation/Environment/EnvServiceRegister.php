<?php

namespace Artificers\Foundation\Environment;

use Artificers\Supports\ServiceRegister;

class EnvServiceRegister extends ServiceRegister {
    public function register(): void {
        $this->rXsiApp->singleton('env', function($container) {
            return new Env($container->get('path.base'));
        });
    }
}