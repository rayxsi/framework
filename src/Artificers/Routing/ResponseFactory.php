<?php
declare(strict_types=1);
namespace Artificers\Routing;

use Artificers\Container\Container;
use Artificers\Http\Concern\AboutResponseBag;

/**
 * Response factory will work with response classes.
 *
 *
 * @author Topu <toerso.mechanix@gmail.com>
 */
class ResponseFactory {
    use AboutResponseBag;

    protected Container $container;
    protected array $headers = [];

    /**
     * @param Container $container Container class instance.
     */
    public function __construct(Container $container) {
        $this->container = $container;
    }
}