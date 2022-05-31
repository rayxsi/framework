<?php

namespace Artificers\Test;

class PrintTest {
    public static function formattingDump($param):void {
        echo "<pre>";
        var_dump($param);
        echo "</pre>";
    }

    public function dump($param): void {
        var_dump($param);
    }
}