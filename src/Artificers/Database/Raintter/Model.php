<?php

namespace Artificers\Database\Ritter;

use Artificers\Database\Concern\InteractWithInputValidation;

abstract class Model {
    use InteractWithInputValidation;

    protected string $_table;

    public function save(): void {
//        var_dump($this->rules);
    }
}