<?php

namespace Artificers\Database\Concern;

use Artificers\Support\Concern\Regex;

trait InteractWithInputValidation {
    use Regex;

    private string $separateByPipes = '/(?=[^|])(?:[^|]*\([^)]+\))*[^|]*/';
    private string $separateByColon = '/(?=[^:])(?:[^:]*\([^)]+\))*[^:]*/';
    private string $standardPwdPattern = '/^(?=.*[!@#$%^&*-])(?=.*[0-9])(?=.*[A-Z]).{8,20}$/';

    private array $rules = [
        'required' =>'required',
        'unique' => 'unique',
        'email' => 'email',
        'min' => 'min',
        'max' => 'max',
        'match' => 'match',
        'standard' => 'standard',
        'salt' => 'salt'
    ];

    protected array $rulesMessages = [
        'required' => '{{@attribute}} is required',
        'unique' => '{{@attribute}} must be unique',
        'email' => '{{@attribute}} must be valid',
        'standard' => '{{@attribute}} must have at least one digit, one uppercase letter and one special character',
        'min' => '{{@attribute}} must be minimum {{@min}} characters long',
        'max' => '{{@attribute}} must be maximum {{@max}} characters long',
        'match' => '{{@attribute}} must be same as {{@match}}',
    ];

    protected array $errorMessages = [];

    public function validate(array $rulesArray): bool|array {
        foreach($rulesArray as $attribute=>$rulesAsString) {
            $value = $this->{$attribute};
            $rules = $this->matchAll($this->separateByPipes, $rulesAsString);

            foreach($rules as $rule) {
                if(str_contains($rule, ':')) {
                   $rulesOfColon = $this->matchAll($this->separateByColon, $rule);
                   //var_dump(strcmp($this->{$rulesOfColon[1]}, $value));
                   $this->check($attribute, $rulesOfColon, $value);
                }else {
                    $this->check($attribute, $rule, $value);
                }
            }
        }

        return empty($this->errorMessages) ? true : $this->errorMessages;
    }

    private function check(string $attribute, string|array $rule, $value): void {
        //checking required field
       if($rule === $this->rules['required'] && empty($value)) $this->addError($attribute, $this->rules[$rule]);

       //checking valid email
       if($rule === $this->rules['email'] && !filter_var($value, FILTER_VALIDATE_EMAIL)) $this->addError($attribute, $this->rules[$rule]);

       if($rule === $this->rules['standard'] && !$this->match($this->standardPwdPattern, $value)) $this->addError($attribute, $this->rules[$rule]);

       //checking minimum and maximum character limit
       if($rule[0] === $this->rules['min'] && strlen($value) < $rule[1]) $this->addError($attribute, $this->rules[$rule[0]], $rule[1]);
       if($rule[0] === $this->rules['max'] && strlen($value) > $rule[1]) $this->addError($attribute, $this->rules[$rule[0]], $rule[1]);

       //checking match field
        if($rule[0] === $this->rules['match'] && strcmp($this->{$rule[1]}, $value) !== 0) $this->addError($attribute, $this->rules[$rule[0]], $rule[1]);
    }

    private function addError(string $attribute, string $rule, $constrain = ''): void {
        $message = str_replace('{{@attribute}}', ucfirst($attribute), $this->rulesMessages[$rule]);

        //checking colon rules
        if($rule === $this->rules['min'])
            $message = str_replace('{{@min}}', $constrain, $message);
        else if($rule === $this->rules['max']) $message = str_replace('{{@max}}', $constrain, $message);
        else if($rule === $this->rules['match']) $message =str_replace('{{@match}}', $constrain, $message);

        $this->errorMessages[$attribute][] = $message;
    }
}