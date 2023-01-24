<?php
declare(strict_types=1);
namespace Artificers\Http\Concern;

use Artificers\Utility\Str;

/**
 * AboutContentTypes works with all the Content-Type headers.
 *
 *
 * @author Topu <toerso.mechanix@gmail.com>
 */
trait AboutContentTypes {
    /**
     * Check the requested MIME TYPE is json or not.
     *
     * @return bool True or false.
     */
    public function isJson(): bool {
       return Str::contains($this->header('Content-Type'), ['/json', '+json']);
    }

    /**
     * Check the requested MIME TYPE is jwt or not.
     *
     * @return bool  True or false.
     */
    public function isJWT(): bool {
        return Str::contains($this->header('Content-Type'), 'jwt');
    }
}