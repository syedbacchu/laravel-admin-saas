<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NationalIdBD implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_numeric($value)) {
            $fail('The :attribute must be a numeric value.');
            return;
        }

        if (!preg_match('/^\d{10}$|^\d{13}$|^\d{17}$/', $value)) {
            $fail('The :attribute must be 10, 13, or 17 digits long.');
        }
    }
}
