<?php

namespace Artificers\View;

use Artificers\Cache\ViewCache;
use Artificers\Container\Container;
use Artificers\Treaties\View\CompilerTreaties;
use Artificers\Treaties\View\EngineTreaties;

class Generator {
    protected Container $container;
    protected EngineTreaties $engine;
    public CompilerTreaties $compiler;

    public function __construct(EngineTreaties $engine, CompilerTreaties $compiler, Container $container = null) {
        $this->container = $container ?: new Container();
        $this->engine = $engine;
        $this->compiler = $compiler;

        $viewCache = $this->container['cache']->get('view') ?? new ViewCache();
        $this->container['cache']->set('view', $viewCache);
    }

    /**
     * Generate the view.
     *
     * @return void
     */
    public function generate(): void {
        //1. Compile the server side js file.
        $jsonOfViewWithState = $this->compiler->compile($this->container['path.ssr'].DIRECTORY_SEPARATOR.'server.js');

        //2. Collect template markup layout
        $layoutMarkup = file_get_contents($this->container['path.view'].DIRECTORY_SEPARATOR.'main.croxo.php');

        //Decode the json of view.
        $ui = json_decode($jsonOfViewWithState);

        //Generate script tag with state.
        $layoutMarkupWithState = $this->handleUiState($layoutMarkup, $ui);

        $viewCache = $this->container['cache']->get('view');

        $viewCache->set($this->injectUiMarkup($layoutMarkupWithState, $ui));
//
//        $this->container['cache']->set('view', $this->viewCache);
    }


    /**
     * Generate script tag with state,
     *
     * @param string $layoutMarkup
     * @param object $ui
     * @return string
     */
    private function handleUiState(string $layoutMarkup, object $ui): string {
        if(!str_contains($layoutMarkup, "{{@preState}}")) {
            return '';
        }

        $scriptTag = '<script>window.__usdx96='.json_encode((array)$ui->initialState, JSON_HEX_TAG|JSON_HEX_QUOT|JSON_HEX_APOS|JSON_HEX_AMP|JSON_NUMERIC_CHECK).';</script>';

        return str_replace('{{@preState}}', $scriptTag, $layoutMarkup);
    }

    /**
     * Inject ui markup in main layout.
     *
     * @param string $layoutMarkup
     * @param object $ui
     * @return string
     */
    private function injectUiMarkup(string $layoutMarkup, object $ui): string {
        if(str_contains($layoutMarkup, '{{@mui-styles}}')) {
            $layoutMarkupWthCss = str_replace("{{@mui-styles}}", $ui->css, $layoutMarkup);
            return str_replace("{{@contents}}", $ui->html, $layoutMarkupWthCss);
        }

        return str_replace("{{@contents}}", $ui->html, $layoutMarkup);
    }
}