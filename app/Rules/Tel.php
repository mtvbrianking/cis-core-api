<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Tel implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Validate telephone number format.
     *
     * Must start with the country zip; like +256785951456
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return (bool) preg_match('/^\+(?:[0-9] ?){9,16}[0-9]$/', $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute format is invalid.';
    }
}
