<?php
declare(strict_types=1);

namespace Artificers\Http\Concern;

/**
 * AboutInputs concern about the request's inputs.
 *
 *
 * @author Topu <toerso.mechanix@gmail.com>
 */
trait AboutInputs {
    /**
     * Retrieve item from desired bag.
     * @param string        $source     Source name.
     * @param string        $key        Key that value has to retrieve.
     * @param string|null   $default    Default value of this key.
     * @return mixed                    All if key is not set. Otherwise, returns the value of that key. If key doesn't exist then return the default value.
     */
    public function retrieveItemFrom(string $source, string $key, string $default = null): mixed {
        if(empty($key)) {
            return $this->$source->all();
        }

        return $this->$source->get($key, $default);
    }

    /**
     * Get desired header from the headers.
     * @param string        $key        Header key that has to retrieve.
     * @param string|null   $default    Default value of this key.
     * @return mixed                    All if key is not set. Otherwise, returns the value of that key. If key doesn't exist then return the default value.
     */
    public function header(string $key, string $default = null): mixed {
        return $this->retrieveItemFrom("headers", $key, $default);
    }

    /**
     * Get the desired server info from the server.
     * @param string        $key        Server key that has to retrieve.
     * @param string|null   $default    Default value of this key.
     * @return mixed                    All if key is not set. Otherwise, returns the value of that key. If key doesn't exist then return the default value.
     */
    public function server(string $key, string $default = null): mixed {
        return $this->retrieveItemFrom('server', $key, $default);
    }
}