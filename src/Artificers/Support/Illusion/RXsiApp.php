<?php
declare(strict_types=1);
namespace Artificers\Support\Illusion;

use Artificers\Design\Patterns\Illusion;

/**
 * @method static basePath(string $path): string
 * @method static getTmpPath(): string
 */
class RXsiApp extends Illusion {
    protected static function getIllusionAccessor(): string {
        return 'rXsiApp';
    }
}