<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidatePasswordRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public $pattren;
    public $message;
    public function __construct($pattren='/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d\W_]{6,}$/',$message=null)
    {
        $this->pattren=$pattren;
        $this->message=$message;
    }
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(!preg_match($this->pattren,$value)){
            $fail($this->message?:':attribute is must alpha numeric with one uppercase letter and minimum 6 characters');
        }
    }
}
