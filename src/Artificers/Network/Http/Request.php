<?php
namespace Artificers\Network\Http;

use Artificers\Test\PrintTest;

class Request {
    public static array $server;

    public static function capture(): void {
        self::$server = $_SERVER;
    }

    public function method(): string {
        return strtolower(self::$server["REQUEST_METHOD"]);
    }

    public function uri(): string {
        $pattern = '/.*(?=\?)/i';
       if(preg_match_all($pattern, self::$server['REQUEST_URI'], $matches)) return $matches[0][0];

       return self::$server['REQUEST_URI'] ?? '/';
    }
}