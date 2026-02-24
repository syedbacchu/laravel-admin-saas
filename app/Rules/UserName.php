<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserName implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Check if the username starts with a letter and contains no spaces
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_-]{0,49}$/', $value)) {
            $fail('The :attribute must start with a letter and can only contain letters, numbers, dashes, or underscores, and be no longer than 50 characters.');
        }

        // Check if the username contains any whitespace
        if (preg_match('/\s/', $value)) {
            $fail('The :attribute must not contain any whitespace.');
        }
    }
}
