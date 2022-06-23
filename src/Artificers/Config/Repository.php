<?php
namespace Artificers\Config;

class Repository {

    /**
     * Store all the config items.
     *
     * @var array
     */
    protected array $items = [];

    /**
     * Set configuration into repository.
     *
     * @param string $key
     * @param string|array|null $value
     * @return void
     */
    public function set(string $key, string|array|null $value = null): void {

        $this->items[$key] = $value;
    }

    protected function collectKeys(string $key): array {
       return str_contains($key, '.') ? explode('.', $key) : [$key];
    }

    public function get() {

    }
}