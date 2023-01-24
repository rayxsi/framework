<?php
declare(strict_types=1);
namespace Artificers\Http;

use Artificers\Support\Concern\Regex;
use Artificers\Support\JSON;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * Request class handle all about http request. It extends Symfony\Component\HttpFoundation\Request base class.
 *
 *
 * @author Topu <toerso.mechanix@gmail.com>
 */
class Request extends SymfonyRequest{
    use Regex, Concern\AboutContentTypes, Concern\AboutInputs;

    /**
     * The decoded JSON content for the request.
     *
     * @var ParameterBag|null
     */
    protected ?ParameterBag $json;

    /**
     * Take a snap of current request.
     * @return Request The current Request instance.
     */
    public static function snap(): Request {
       return parent::createFromGlobals();
   }

    /**
     * Get serialized server information.
     * @return array    An array of server information.
     */
    public function getSerializedServerInfo(): array {
        $server = [];

        $server["SERVER_PORT"] = $this->getPort();
        $server["REQUEST_URI"] = urldecode($this->getRequestUri());
        $server["REQUEST_METHOD"] = $this->getMethod();
        $server["HTTP_HOST"] = $this->getHttpHost();

        return $server;
   }

    /**
     * Get the http host.
     * @return string   Http host.
     */
    public function root(): string {
       return rtrim($this->getSchemeAndHttpHost(), '/');
   }

    /**
     * Get the URL for the request without query string.
     *
     * @return string   Requested URL without query string.
     */
    public function url(): string {
        return rtrim($this->replace('/\?.*/', '', $this->getUri()), '/');
    }

    /**
     * Get the request uri without query string.
     * @return string   Requested uri without query string.
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
     * @return string   The current path.
     */
    public function path(): string {
        $pattern = trim($this->getPathInfo(), '/');

        return $pattern === '' ? '/' : $pattern;
    }

    /**
     * Determine if the request is the result of an AJAX call.
     *
     * @return bool  True or false.
     */
    public function isAjax(): bool{
        return $this->isXmlHttpRequest();
    }

    /**
     * Determine if the request is the result of a PJAX call.
     *
     * @return bool True or false.
     */
    public function isPjax(): bool{
        return $this->headers->get('X-PJAX') == true;
    }

    /**
     * Get the client IP address.
     *
     * @return string|null  Client IP address.
     */
    public function ip(): ?string {
        return $this->getClientIp();
    }

    /**
     * Get the client IP addresses.
     *
     * @return array    An array of client IP addresses.
     */
    public function ips(): array {
        return $this->getClientIps();
    }

    /**
     * Determine if the request is over HTTPS.
     *
     * @return bool True or false.
     */
    public function secure(): bool {
        return $this->isSecure();
    }

    /**
     * Get the client user agent.
     *
     * @return string|null User-agent/browser name and version.
     */
    public function userAgent(): ?string {
        return $this->headers->get('User-Agent');
    }

    /**
     * Convert json data to key->value pair into parameter bag and return ParameterBag instance.
     * @return ParameterBag|null \Symfony\Component\HttpFoundation\ParameterBag instance or null.
     */
    protected function jsonAble(): ?ParameterBag {
        if(!isset($this->json)) {
            $this->json = new ParameterBag((array)JSON::parse($this->getContent(), true));
        }

        return $this->json;
    }

    /**
     * Get the requested json payload as an array.
     * @return array An array of requested payload.
     */
    public function payload(): array {
       return $this->jsonAble()->all();
   }

    /**
     * Retrieve a value of a key from the requested json payload/content.
     * @param string        $name       Input field name.
     * @param mixed|null    $default    Default value of that field.
     * @return mixed       Input field value.
     */
    public function field(string $name, mixed $default = null): mixed {
       return $this->jsonAble()->get($name, $default);
   }

    /**
     * Return the correct input source based on request.
     * @return ParameterBag|InputBag|null \Symfony\Component\HttpFoundation\ParameterBag instance or null.
     */
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