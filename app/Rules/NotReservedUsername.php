<?php

namespace App\Rules;

use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class NotReservedUsername implements Rule
{
    public function passes($attribute, $value): bool
    {
        if (User::query()->count() <= 1) {
            return true;
        }

        if (! filter_var(settings('block_reserved_usernames', true), FILTER_VALIDATE_BOOLEAN)) {
            return true;
        }

        $reserved = config('reserved-usernames.list', []);

        return ! in_array(strtolower((string) $value), array_map('strtolower', $reserved), true);
    }

    public function message(): string
    {
        return 'This username is reserved and cannot be used.';
    }
}
