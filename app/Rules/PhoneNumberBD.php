<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhoneNumberBD implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!preg_match('/^[0-9]{11}$/', $value)) {
            $fail('The :attribute must be a number and exactly 11 digits long.');
            return;
        }

        // Allowed prefixes
        $allowedPrefixes = ['019', '017', '015', '013', '014', '016', '018'];

        // Check if the value starts with one of the allowed prefixes
        $prefixValid = false;
        foreach ($allowedPrefixes as $prefix) {
            if (substr($value, 0, 3) === $prefix) {
                $prefixValid = true;
                break;
            }
        }

        if (!$prefixValid) {
            $fail('The :attribute must start with a valid prefix (019, 017, 015, 013, 014, 016, 018).');
        }
    }
}
