<?php
declare(strict_types=1);

namespace Artificers\Http\Concern;

use Artificers\Support\Str;

trait AboutContentTypes {
    /**
     * Check the requested MIME TYPE is json or not.
     *
     * @return bool
     */
    public function isJson(): bool {
       return Str::contains($this->header('Content-Type'), ['/json', '+json']);
    }

    /**
     * Check the requested MIME TYPE is jwt or not.
     *
     * @return bool
     */
    public function isJWT(): bool {
        return Str::contains($this->header('Content-Type'), 'jwt');
    }
}