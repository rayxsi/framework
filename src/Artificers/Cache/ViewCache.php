<?php

namespace Artificers\Cache;

class ViewCache {
    private string $view = '';

    public function set(string $content): void {
        $this->view = $content;
    }

    public function get(): string {
        return $this->view;
    }

    public function clean(): static {
        $this->view = "";

        return $this;
    }
}