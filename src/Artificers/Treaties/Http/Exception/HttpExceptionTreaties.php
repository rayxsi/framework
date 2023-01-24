<?php declare(strict_types=1);

/**
 * This file is part of Rayxsi.
 *
 * (c) Topu <toerso.mechanix@gmail.com>
 */
namespace Artificers\Treaties\Http\Exception;

/**
 * Http exception interface.
 *
 * @author Topu <toerso.mechanix@gmail.com>
 */
interface HttpExceptionTreaties extends \Throwable {
    public function getStatusCode(): int;

    public function getHeaders(): array;
}