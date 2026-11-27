<?php

namespace App\Domain\Users\Rules;

use App\Domain\Users\Support\Username;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Format and reserved-word checks. Uniqueness is checked separately with
 * Rule::unique so it can ignore the current user when editing.
 */
final class ValidUsername implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('validation.username.format')->translate();

            return;
        }

        $length = strlen($value);

        if ($length < Username::MIN_LENGTH || $length > Username::MAX_LENGTH) {
            $fail('validation.username.length')->translate([
                'min' => Username::MIN_LENGTH,
                'max' => Username::MAX_LENGTH,
            ]);

            return;
        }

        if (preg_match(Username::PATTERN, $value) !== 1) {
            $fail('validation.username.format')->translate();

            return;
        }

        if (Username::isReserved($value)) {
            $fail('validation.username.reserved')->translate();
        }
    }
}
