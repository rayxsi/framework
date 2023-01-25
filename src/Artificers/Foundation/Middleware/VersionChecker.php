<?php
declare(strict_types=1);
namespace Artificers\Foundation\Middleware;

use Artificers\Foundation\Rayxsi;
use Closure;

class VersionChecker {
    public function __invoke(Rayxsi $rayxsi, Closure $next) {
        if(version_compare($phpVersion = PHP_VERSION, $rayxsiMinVersion = $rayxsi::$minimumPhpVersion, '<')) {
            die(sprintf('You are currently running PHP version[%s], but the Rayxsi framework requires at least PHP version [%s]', $phpVersion, $rayxsiMinVersion));
        }
        return $next($rayxsi);
    }
}