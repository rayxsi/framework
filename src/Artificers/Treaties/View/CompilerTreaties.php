<?php

namespace Artificers\Treaties\View;

interface CompilerTreaties {
    public function compile(string $file): string;
}