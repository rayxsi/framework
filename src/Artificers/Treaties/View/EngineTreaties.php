<?php
    namespace Artificers\Treaties\View;

    interface EngineTreaties {
        public function run(string $script): string;
        public function getDispatchHandler(): string;
    }