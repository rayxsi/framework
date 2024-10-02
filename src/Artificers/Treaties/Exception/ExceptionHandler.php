<?php declare(strict_types=1);

namespace Artificers\Treaties\Exception;

use Artificers\Http\Request;
use Throwable;

interface ExceptionHandler {
    public function throw(Request $request, Throwable $e);

    public function report(Throwable $e);

    public function render(Request $request, Throwable $e);
}