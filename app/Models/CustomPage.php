<?php

namespace App\Models;

use App\Actions\CustomPageActions;
use Illuminate\Database\Eloquent\Model;

class CustomPage extends Model
{
    protected $table = 'pages';

    protected $fillable = [
        'title',
        'slug',
        'status',
        'content',
    ];

    public static function actions(): CustomPageActions
    {
        return new CustomPageActions;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
