<?php
namespace Artificers\Foundation\Environment;
use Dotenv\Dotenv;

class Env {
    private string $basePath;

    public function __construct($basePath) {
        $this->basePath = $basePath;
    }

    public function load():void {
        Dotenv::createImmutable($this->basePath)->load();
    }
}