<?php
declare(strict_types=1);

namespace Artificers\Http\Concern;

use Symfony\Component\HttpFoundation\InputBag;

trait AboutInputs {
    /**
     * Retrieve item from desired bag.
     * @param string $source
     * @param string $key
     * @param string|null $default
     * @return mixed
     */
    public function retrieveItemFrom(string $source, string $key, string $default = null): mixed {
        if(empty($key)) {
            return $this->$source->all();
        }

        return $this->$source->get($key, $default);
    }

    /**
     * Get desired header from the headers.
     * @param string $key
     * @param string|null $default
     * @return mixed
     */
    public function header(string $key, string $default = null): mixed {
        return $this->retrieveItemFrom("headers", $key, $default);
    }

    /**
     * Get the desired server info from the server.
     * @param string $key
     * @param string|null $default
     * @return mixed
     */
    public function server(string $key, string $default = null): mixed {
        return $this->retrieveItemFrom('server', $key, $default);
    }
}