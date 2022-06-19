<?php
namespace Artificers\Http;

use Artificers\Supports\Concern\Regex;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request extends SymfonyRequest{
    use Regex;

   public static function snap(): Request {
       return parent::createFromGlobals();
   }
}