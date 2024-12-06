<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BuyperProcessRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    protected $method;
    public function __construct($method)
    {
        $this->method = $method;
    }
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        method_exists($this,$this->method) ? call_user_func([$this,$this->method],$value,$fail) : $fail('no method found ');
    }
    public function not_in_arr($request_value,$fail){
        if(in_array(0,$request_value)){
            $fail("You can't provide 0 in number of items");
            return;
        }
    }
}
