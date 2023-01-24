<?php declare(strict_types=1);

/**
 * This file is part of Rayxsi.
 *
 * (c) Topu <toerso.mechanix@gmail.com>
 */
namespace Artificers\Http\Exception;

use Artificers\Treaties\Http\Exception\HttpExceptionTreaties;
use Throwable;

/**
 * Http exception base class.
 *
 * @author Topu <toerso.mechanix@gmail.com>
 */
class HttpException extends \RuntimeException implements HttpExceptionTreaties {
    protected int $statusCode;
    protected array $headers;

    public function __construct(int $statusCode, string $message = "", array $headers = [], ?Throwable $previous = null, int $code = 0) {
        $this->statusCode = $statusCode;
        $this->headers = $headers;

        parent::__construct($message, $code, $previous);
    }

    public function setHeaders(array $headers): void {
        $this->headers = $headers;
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }

    public function getHeaders(): array {
        return $this->headers;
    }
}