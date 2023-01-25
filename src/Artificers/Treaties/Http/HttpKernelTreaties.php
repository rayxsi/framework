<?php

namespace Artificers\Treaties\Http;
use Artificers\Http\Request;

interface HttpKernelTreaties {
    public function resolve(Request $request);
}