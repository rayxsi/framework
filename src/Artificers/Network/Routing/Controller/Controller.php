<?php

namespace Artificers\Network\Http\Controller;

use Artificers\Foundation\Application;

class Controller {
    public static function view(): string {
        return Application::$app->viewKernel->render();
    }
}