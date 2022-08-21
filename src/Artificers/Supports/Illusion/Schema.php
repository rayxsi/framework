<?php

namespace Artificers\Supports\Illusion;
use Closure;

/**
* @method static \Artificers\Database\Lizie\Schema\Schema make(string $tableName, Closure|null $closure): void
* @method static \Artificers\Database\Lizie\Schema\Schema dropTableIfExists(string $tableName): void
* @method static \Artificers\Database\Lizie\Schema\Schema modify(string $tableName, Closure $callback): void
* @method static \Artificers\Database\Lizie\Schema\Schema exists(string $table): bool
* @method static \Artificers\Database\Lizie\Schema\Schema dropIfExists(): bool
* @method static \Artificers\Database\Lizie\Schema\Schema createDatabase(string $name): bool
* @method static \Artificers\Database\Lizie\Schema\Schema use(string $database): bool
* @method static \Artificers\Database\Lizie\Schema\Schema rename(string $old, string $new): bool
* @method static \Artificers\Database\Lizie\Schema\Schema enableForeignKeyConstraints(): bool
* @method static \Artificers\Database\Lizie\Schema\Schema disableForeignKeyConstraints(): bool
* @method static \Artificers\Database\Lizie\Schema\Schema getAllTable(): array
 */
class Schema extends Illusion {
    protected static function getIllusionAccessor(): string {
        return "db.schema";
    }
}