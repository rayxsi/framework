<?php

namespace Artificers\View;

use Artificers\Support\ServiceRegister;
use Artificers\View\Engines\Croxo;

class ViewServiceRegister extends ServiceRegister {
    public function register(): void {
        $engine = new Croxo($this->rXsiApp['path.node'], $this->rXsiApp['path.tmp']);
        $compiler = new Compiler($engine);

        $this->rXsiApp->singleton('view', function($container) use($engine, $compiler) {
            return new Generator($engine, $compiler, $container);
        });
    }
}