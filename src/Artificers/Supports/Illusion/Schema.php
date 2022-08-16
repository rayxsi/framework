<?php

namespace Artificers\Supports\Illusion;
use Closure;

/**
* @method static \Artificers\Database\Lizie\Schema\Schema make(string $tableName, Closure|null $closure): void
* @method static \Artificers\Database\Lizie\Schema\Schema dropTableIfExists(string $tableName): void
* @method static \Artificers\Database\Lizie\Schema\Schema table(string $name, Closure $callback): void
* @method static \Artificers\Database\Lizie\Schema\Schema exists(string $table): bool
* @method static \Artificers\Database\Lizie\Schema\Schema dropIfExists(): bool
* @method static \Artificers\Database\Lizie\Schema\Schema createDatabase(string $name): bool
* @method static \Artificers\Database\Lizie\Schema\Schema use(string $database): bool
 */
class Schema extends Illusion {
    protected static function getIllusionAccessor(): string {
        return "db.schema";
    }
}