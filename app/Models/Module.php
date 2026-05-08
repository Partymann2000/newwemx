<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Module extends Extension
{
    /**
     * The "booted" method of the model.
     *
     * This is where we add the global scope.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope('module', function (Builder $builder) {
            $builder->where('type', 'module');
        });
    }
}
