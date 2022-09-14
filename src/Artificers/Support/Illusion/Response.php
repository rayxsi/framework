<?php
declare(strict_types=1);
namespace Artificers\Support\Illusion;

use Artificers\Design\Patterns\Illusion;
use Closure;

/**
* @method static \Artificers\Routing\ResponseFactory back(mixed $content, int $status=200, array $headers=[]): Response
* @method static \Artificers\Routing\ResponseFactory  with(array $headers): static
* @method static \Artificers\Routing\ResponseFactory  stream(Closure $callback, int $status = 200, array $headers = []): StreamedResponse
* @method static \Artificers\Routing\ResponseFactory  jsonp(string $callback, mixed $data = [], int $status = 200, array $headers = [], int $options = 0, bool $json = false): JsonResponse
* @method static \Artificers\Routing\ResponseFactory  json(mixed $data, int $status = 200, array $headers = [], int $options = 0, bool $json = false): JsonResponse
*/
class Response extends Illusion {
    protected static function getIllusionAccessor(): string {
        return 'response.factory';
    }
}