<?php

namespace Artificers\Database\Raintter\Relations;

class OneToOne {
    protected array $relationWith = [];

    public function __construct(array $relationWith) {
        $this->relationWith = $relationWith;
    }

    public function getFKeys(): array {
        $keys = [];

        foreach($this->relationWith as $class) {
            $keys[$class::TABLE_NAME] = $class::PRIMARY_KEY;
        }

        return $keys;
    }
}