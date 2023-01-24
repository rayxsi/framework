<?php

namespace Artificers\View;

use Artificers\Treaties\View\CompilerTreaties;
use Artificers\View\Engines\Croxo;

class Compiler implements CompilerTreaties {
    protected Croxo $engine;
    protected  array $server;

    public function __construct(Croxo $engine){
        $this->engine = $engine;

    }

    public function compile(string $file):string {
        $low = array_change_key_case($this->server, CASE_LOWER);
        $requestJson = json_encode($low);

        $script = implode(";", [
            "const __server = $requestJson",
            "let dispatcher = {$this->engine->getDispatchHandler()}",
            file_get_contents($file)
        ]);

        return $this->engine->run($script);
    }

    public function bindServer(array $server) {
        $this->server = $server;
    }
}