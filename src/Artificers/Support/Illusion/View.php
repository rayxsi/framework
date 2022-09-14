<?php
declare(strict_types=1);
namespace Artificers\Support\Illusion;

use Artificers\Design\Patterns\Illusion;

/**
* @method static \Artificers\View\View generate(): View
 */
class View extends Illusion {
    protected static function getIllusionAccessor(): string {
        return 'view';
    }
}