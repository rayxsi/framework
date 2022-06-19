<?php

namespace Artificers\Treaties\Http;
use Artificers\Network\Http\Request;

interface HttpKernelTreaties {
    public function resolve(Request $request);
}