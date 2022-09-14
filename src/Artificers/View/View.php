<?php

namespace Artificers\View;

use Artificers\Treaties\Support\Stringable;

class View implements Stringable {
    protected string $content;

    public function __construct(string $content) {
        $this->content = $content;
    }

    public function toString(): string {
        return $this->content;
    }
}