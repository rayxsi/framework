<?php

namespace Artificers\Foundation\View;

use Artificers\Foundation\Application;
use Artificers\View\Engines\Croxo;
use Artificers\View\Renderer;

class Kernel {
    private Application $app;
    private Renderer $renderer;

    private object $ui;
    private string $script;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function render(): string {
        $viewWithJson = $this->renderer->render($this->app->getSsrFile());
        $layoutMarkup = file_get_contents($this->app->getViewLayout());
        $this->ui = json_decode($viewWithJson);
        $layoutMarkupWithState = $this->handleUiState($layoutMarkup);

        return $this->injectUiMarkup($layoutMarkupWithState);
    }

    private function handleUiState(string $layoutMarkup): string{
        if(str_contains($layoutMarkup, "{{@preState}}")) {
            //generate script tage with initial state
            $this->script = '<script>window.__usdx96='.json_encode((array)$this->ui->initialState, JSON_HEX_TAG|JSON_HEX_QUOT|JSON_HEX_APOS|JSON_HEX_AMP|JSON_NUMERIC_CHECK).';</script>';
        }

        return str_replace('{{@preState}}', $this->script, $layoutMarkup);
    }

    private function injectUiMarkup(string $layoutMarkup): string {
            if(str_contains($layoutMarkup, '{{@mui-styles}}')) {
                $layoutMarkupWthCss = str_replace("{{@mui-styles}}", $this->ui->css, $layoutMarkup);
                return str_replace("{{@contents}}", $this->ui->html, $layoutMarkupWthCss);
            }

        return str_replace("{{@contents}}", $this->ui->html, $layoutMarkup);
    }

    public function loadEngine(array $server): void {
        $croxoEngine = new Croxo($this->app->getNodejs(), $this->app->getTmpDir());
        $this->renderer = new Renderer($croxoEngine, $server);
    }
}