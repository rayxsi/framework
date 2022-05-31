<?php

namespace Artificers\Network\Routing\Controller;

use Artificers\Foundation\Application;

class Controller {
    private static string $content;

    public static function view(): string {
        try {
            self::$content = Application::$app->viewKernel->render();
        }catch(\Exception $e) {
            return $e->getMessage();
        }

        return self::$content;
    }
}