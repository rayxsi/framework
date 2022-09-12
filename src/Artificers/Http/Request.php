<?php
declare(strict_types=1);

namespace Artificers\Http;

use Artificers\Http\Concern\AboutContentTypes;
use Artificers\Http\Concern\AboutInputs;
use Artificers\Support\Concern\Regex;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request extends SymfonyRequest{
    use Regex, AboutContentTypes, AboutInputs;

    /**
     * The decoded JSON content for the request.
     *
     * @var ParameterBag|null
     */
    protected ?ParameterBag $json;

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

   public function root(): string {
       return rtrim($this->getSchemeAndHttpHost(), '/');
   }

    /**
     * Get the URL for the request without query string.
     *
     * @return string
     */
    public function url(): string {
        return rtrim(preg_replace('/\?.*/', '', $this->getUri()), '/');
    }

    /**
     * Get the request uri without query string.
     * @return string
     */
    public function getRequestPath(): string {
        if(preg_match("/(.*)(?=\?)/", $this->getRequestUri(), $matches)) {
            return $matches[1];
        }

        return $this->getRequestUri();
    }

    /**
     * Get the current path info for the request.
     *
     * @return string
     */
    public function path(): string {
        $pattern = trim($this->getPathInfo(), '/');

        return $pattern === '' ? '/' : $pattern;
    }

    /**
     * Determine if the request is the result of an AJAX call.
     *
     * @return bool
     */
    public function isAjax(): bool{
        return $this->isXmlHttpRequest();
    }

    /**
     * Determine if the request is the result of a PJAX call.
     *
     * @return bool
     */
    public function isPjax(): bool{
        return $this->headers->get('X-PJAX') == true;
    }

    /**
     * Get the client IP address.
     *
     * @return string|null
     */
    public function ip(): ?string {
        return $this->getClientIp();
    }

    /**
     * Get the client IP addresses.
     *
     * @return array
     */
    public function ips(): array {
        return $this->getClientIps();
    }

    /**
     * Determine if the request is over HTTPS.
     *
     * @return bool
     */
    public function secure(): bool {
        return $this->isSecure();
    }

    /**
     * Get the client user agent.
     *
     * @return string|null
     */
    public function userAgent(): ?string {
        return $this->headers->get('User-Agent');
    }

    protected function jsonAble(): ?ParameterBag {
        if(!isset($this->json)) {
            $this->json = new ParameterBag((array)json_decode($this->getContent(), true));
        }

        return $this->json;
    }

   public function payload(): array {
       return $this->jsonAble()->all();
   }

   public function field(string $key, mixed $default = null): mixed {
       return $this->jsonAble()->get($key, $default);
   }

   public function inputSource(): ParameterBag|InputBag|null {
        if($this->isJson()) {
            return $this->jsonAble();
        }
        return in_array($this->getRealMethod(), ['GET', 'HEAD']) ? $this->query : $this->request;
   }

   public function __get(string $key) {
       return $this->retrieveItemFrom('query', $key);
   }
}