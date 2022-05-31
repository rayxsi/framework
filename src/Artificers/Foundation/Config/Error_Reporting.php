<?php

namespace Artificers\Foundation\config;

class Error_Reporting {
    private string $basePath;

    public function __construct($basePath) {
        $this->basePath = $basePath;
    }

    public function _set_reporting(): void {
        if($_ENV['DEBUG_MODE'] === 'true') {
            error_reporting(E_ALL);
            ini_set("display_errors", 1);
        }else {
            error_reporting(0);
            ini_set('display_errors', 0);
            ini_set('log_errors', TRUE);
            ini_set('error_log', $this->basePath.'/tmp/logs/errors.log');
        }
    }

}