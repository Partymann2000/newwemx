<?php

namespace App\Actions;

abstract class Action
{
    /**
     * Remove null fields so optional inputs don't overwrite values.
     */
    protected static function omitNullValues(array $data): array
    {
        return array_filter($data, static fn ($value) => $value !== null);
    }
}
