<?php

namespace Artificers\Cache;

use Artificers\Container\Container;
use Artificers\Utilities\Ary;

class CacheManager {
    private array $storage = [];
    private Container $container;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    public function set(string $key, object $object): void {
        $this->storage[$key] = $object;
    }

    public function get(string $key): object|null {
        return $this->storage[$key] ?? null;
    }

    public function remove(string $key): static {
        if(Ary::keyExists($key, $this->storage)) {
            unset($this->storage[$key]);
        }

        return $this;
    }

    public function clean(): static {
        $this->storage = [];
        return $this;
    }
}