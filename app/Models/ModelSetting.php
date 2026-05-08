<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ModelSetting extends Model
{
    protected $table = 'model_settings';

    protected $fillable = ['key', 'value'];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
