<?php

namespace Artificers\Database\Lizie\Schema;

use ArrayAccess;

class Command implements ArrayAccess {
    private array $attributes = [];

    public function __construct(array $attributes) {
        foreach($attributes as $key=>$attribute) {
            $this->attributes[$key] = $attribute;
        }
    }

    /**
     * @inheritDoc
     */
    public function offsetExists(mixed $offset): bool {
        return isset($this->attributes[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet(mixed $offset): mixed {
        return $this->attributes[$offset];
    }

    /**
     * @inheritDoc
     */
    public function offsetSet(mixed $offset, mixed $value): void {
       $this->attributes[$offset] = $value;
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset(mixed $offset): void {
        unset($this->attributes[$offset]);
    }
}