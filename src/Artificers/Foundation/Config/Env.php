<?php
namespace Artificers\Foundation\Config;

use Dotenv\Dotenv;

class Env {
    public static function load():void {
        Dotenv::createImmutable(BASE_PATH)->load();
    }
}