<?php

namespace Artificers\Support\Illusion;
/**
* @method static \Artificers\Database\Lizie\Query\Builder from(string $table, string $alias=''): static
* @method static \Artificers\Database\Lizie\Query\Builder table(string $table, string $alias=''): static
 */
class QB extends Illusion {
    protected static function getIllusionAccessor(): string {
        return "db.builder";
    }
}