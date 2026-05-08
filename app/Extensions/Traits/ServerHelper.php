<?php

namespace App\Extensions\Traits;

trait ServerHelper
{
    public function canChangePassword(): bool
    {
        return method_exists($this, 'changePassword');
    }
}
