<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBan extends Model
{
    protected $fillable = [
        'user_id',
        'banned_by_id',
        'lifted_by_id',
        'ip_address',
        'is_ip_ban',
        'reason',
        'expires_at',
        'lifted_at',
    ];

    protected function casts(): array
    {
        return [
            'is_ip_ban' => 'boolean',
            'expires_at' => 'datetime',
            'lifted_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bannedBy()
    {
        return $this->belongsTo(User::class, 'banned_by_id');
    }

    public function liftedBy()
    {
        return $this->belongsTo(User::class, 'lifted_by_id');
    }

    public function isActive(): bool
    {
        if ($this->lifted_at) {
            return false;
        }

        return !$this->expires_at || $this->expires_at->isFuture();
    }
}
