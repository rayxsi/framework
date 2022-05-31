<?php
    namespace Artificers\Treaties\View;

    interface Engine {
        public function run(string $script): string;
        public function getDispatchHandler(): string;
    }