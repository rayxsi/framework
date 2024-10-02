<?php
namespace Artificers\Config;

use Artificers\Utility\Ary;

class Repository {

    /**
     * Store all the configuration items.
     *
     * @var array
     */
    protected array $items = [];

    /**
     * Set configuration into repository.
     *
     * @param string|array $key
     * @param string|array|null $value
     * @return void
     */
    public function set(string|array $key, string|array|null $value = null): void {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach($keys as $key => $value) {
            Ary::set($this->items, $key, $value);
        }
    }

    /**
     * Get the item from repository.
     *
     * @param $key
     * @return mixed
     */
    public function get($key): mixed {
        return Ary::get($this->items, $key);
    }
}