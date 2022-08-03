<?php
declare(strict_types=1);

namespace Artificers\Http;

use Artificers\Supports\Concern\Regex;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request extends SymfonyRequest{
    use Regex;

   public static function snap(): Request {
       return parent::createFromGlobals();
   }

   public function getSerializedServerInfo(): array {
        $server = [];

        $server["SERVER_PORT"] = $this->getPort();
        $server["REQUEST_URI"] = urldecode($this->getRequestUri());
        $server["REQUEST_METHOD"] = $this->getMethod();
        $server["HTTP_HOST"] = $this->getHttpHost();

        return $server;
   }
}