<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderExceptionLog extends Model
{
    protected $table = 'order_exception_logs';

    protected $fillable = [
        'order_id',
        'action',
        'message',
        'file',
        'line',
        'code',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function resolve(): void
    {
        $this->resolved_at = now();
        $this->save();
    }

    public function unresolve(): void
    {
        $this->resolved_at = null;
        $this->save();
    }

    public function isResolved(): bool
    {
        return !is_null($this->resolved_at);
    }
}
