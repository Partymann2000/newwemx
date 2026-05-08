<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNote extends Model
{
    protected $table = 'admin_notes';

    protected $fillable = [
        'admin_id',
        'notable_id',
        'notable_type',
        'content',
        'status',
        'is_private',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function model()
    {
        return $this->morphTo();
    }

    public static function actions(): \App\Actions\AdminNoteActions
    {
        return new \App\Actions\AdminNoteActions();
    }
}
