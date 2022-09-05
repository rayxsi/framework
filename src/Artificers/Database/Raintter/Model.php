<?php

namespace Artificers\Database\Raintter;

use Artificers\Container\Container;
use Artificers\Database\Concern\InteractWithInputValidation;
use Artificers\Treaties\Container\BindingException;
use Artificers\Treaties\Container\NotFoundException;

abstract class Model {
    use InteractWithInputValidation;

    public string $table = '';

    private MDBConjunction $conjunction;

    public function __construct(MDBConjunction $conjunction) {
        $this->conjunction = $conjunction;

        $this->conjunction->set($this);
    }

    public function save(): void {

        dump($this->conjunction->transport($this));
    }

    public function __set(string $name, $value): void {
       $this->$name = $value;
    }
}