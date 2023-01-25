<?php declare(strict_types=1);

namespace Artificers\Treaties\Exception;

use Artificers\Http\Request;
use Throwable;

interface ExceptionHandler {
    public function throw(Request $request, Throwable $exp);

    public function report(Throwable $exp);
}