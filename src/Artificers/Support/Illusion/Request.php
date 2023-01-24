<?php
declare(strict_types=1);
namespace Artificers\Support\Illusion;

use Artificers\Design\Patterns\Illusion;

class Request extends Illusion {
    protected static function getIllusionAccessor(): string {
        return 'request';
    }
}