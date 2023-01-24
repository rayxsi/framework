<?php
declare(strict_types=1);
namespace Artificers\Http\Bag;

use Closure;
use Symfony\Component\HttpFoundation\StreamedResponse as SymfonyStreamedResponse;

/**
 * StreamedResponse represents a streamed HTTP response.
 *
 * A StreamedResponse uses a callback for its content.
 *
 * The callback should use the standard PHP functions like echo
 * to stream the response back to the client. The flush() function
 * can also be used if needed.
 *
 * @see flush()
 *
 * @author Topu <toerso.mechanix@gmail.com>
 */
class StreamedResponse extends SymfonyStreamedResponse {
    /**
     * @param Closure   $callback           A closure that will call.
     * @param int       $status             The http status code.
     * @param array     $headers            Headers that will send with the response to the client.
     */
    public function __construct(Closure $callback, int $status = 200, array $headers = []) {
        parent::__construct($callback, $status, $headers);
    }
}