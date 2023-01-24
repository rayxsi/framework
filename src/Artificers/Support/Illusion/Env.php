<?php
declare(strict_types=1);
namespace Artificers\Support\Illusion;

use Artificers\Design\Patterns\Illusion;

/**
* @method static \Artificers\Foundation\Environment\Env collect(string $key)
 */
class Env extends Illusion {
    protected static function getIllusionAccessor(): string {
        return 'env';
    }
}