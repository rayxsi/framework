<?php

namespace Artificers\Network\Http\Concern;

trait InteractWithInputValidation {
    protected array $rules;

    public function validate(array $rules): array {
        $this->rules = $rules;
        return [];
    }


}