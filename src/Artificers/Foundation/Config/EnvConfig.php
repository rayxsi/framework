<?php
namespace Artificers\Foundation\Config;

use Dotenv\Dotenv;

class EnvConfig {
    private string $path;
    public function __construct($path) {
        $this->path = $path;
    }

    public function load():void {
        $envFile = Dotenv::createImmutable($this->path);
        $envFile->load();
    }
}