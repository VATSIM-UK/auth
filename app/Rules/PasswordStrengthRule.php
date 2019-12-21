<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class PasswordStrengthRule implements Rule
{
    private $errorMessage;

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // 1: Check for at least 8 characters
        if (strlen($value) < 8) {
            $this->errorMessage = trans('validation.min.string', ['min' => 8]);
            return false;
        }

        // 2: Check for at least 1 uppercase character
        if (preg_match_all('/[A-Z]/', $value) < 1) {
            $this->errorMessage = trans('validation.uppercase', ['min' => 1]);
            return false;
        }

        // 3: Check for at least 1 lowercase character
        if (preg_match_all('/[a-z]/', $value) < 1) {
            $this->errorMessage = trans('validation.lowercase', ['min' => 1]);
            return false;
        }

        // 4: Check for at least 1 numerical character
        if (preg_match_all('/[0-9]/', $value) < 1) {
            $this->errorMessage = trans('validation.numbers', ['min' => 1]);
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->errorMessage ?? 'The password does not meet security requirements.';
    }
}
