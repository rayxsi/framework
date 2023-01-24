<?php
declare(strict_types=1);
namespace Artificers\Cache;

use Artificers\Container\Container;
use Artificers\Utility\Ary;

class CacheManager {
    private array $storage = [];
    private Container $container;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    /**
     * Set cache to the cache storage.
     *
     * @param string $key
     * @param object $object
     * @return void
     */
    public function set(string $key, object $object): void {
        $this->storage[$key] = $object;
    }

    /**
     * Get the cache from the cache storage.
     *
     * @param string $key
     * @return object|null
     */
    public function get(string $key): object|null {
        return $this->storage[$key] ?? null;
    }

    /**
     * Remove cache from the cache storage.
     *
     * @param string $key
     * @return $this
     */
    public function remove(string $key): static {
        if(Ary::keyExists($key, $this->storage)) {
            unset($this->storage[$key]);
        }

        return $this;
    }

    /**
     * Clean the cache storage.
     *
     * @return $this
     */
    public function clean(): static {
        $this->storage = [];
        return $this;
    }
}