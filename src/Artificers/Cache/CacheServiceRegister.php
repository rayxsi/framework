<?php

namespace Artificers\Cache;

use Artificers\Supports\ServiceRegister;

class CacheServiceRegister extends ServiceRegister {
    public function register(): void {
       $this->rXsiApp->singleton('cache', function($container) {
           return new CacheManager($container);
       });
    }
}