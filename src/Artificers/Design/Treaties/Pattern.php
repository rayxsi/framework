<?php

namespace Artificers\Design\Treaties;

interface Pattern {
    public function send(mixed $arg): static;
    public function through(mixed $pipes): static;
}