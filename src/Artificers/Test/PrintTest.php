<?php

namespace Artificers\Test;

class Io {
    public function formattingDump($param):void {
        echo "<pre>";
        var_dump($param);
        echo "</pre>";
    }

    public function dump($param): void {
        var_dump($param);
    }
}