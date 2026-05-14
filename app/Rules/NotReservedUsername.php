<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NotReservedUsername implements Rule
{
    public function passes($attribute, $value): bool
    {
        $reserved = config('reserved-usernames.list', []);

        return ! in_array(strtolower((string) $value), array_map('strtolower', $reserved), true);
    }

    public function message(): string
    {
        return 'This username is reserved and cannot be used.';
    }
}
