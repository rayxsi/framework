<?php

namespace Artificers\Database;

use Artificers\Database\Concern\InteractWithInputValidation;

/**
 * @property  mixed $email
 * @property mixed $password
 */
abstract class Model {
    use InteractWithInputValidation;

    protected string $tableName;
    protected array $inputFields;

    public function save(): void {
//        var_dump($this->rules);
    }
}