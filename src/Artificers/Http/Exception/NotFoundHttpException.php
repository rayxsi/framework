<?php declare(strict_types=1);

/**
 * This file is part of Rayxsi.
 *
 * (c) Topu <toerso.mechanix@gmail.com>
 */
namespace Artificers\Http\Exception;

use Throwable;

/**
 * Not found http exception class.
 *
 * @author Topu <toerso.mechanix@gmail.com>
 */
class NotFoundHttpException extends HttpException {
    public function __construct(string $message = "", array $headers = [], ?Throwable $previous = null, int $code = 0) {
        parent::__construct(404, $message, $headers, $previous, $code);
    }
}