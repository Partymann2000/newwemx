<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPrice extends Model
{
    protected $table = 'order_prices';

    protected $fillable = [
        'order_id',
        'cycle_price',
        'upgrade_fee',
        'description',
        'type',
        'key',
        'value',
        'data',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cycle_price' => 'decimal:8',
            'upgrade_fee' => 'decimal:8',
            'data' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function packagePrice(): BelongsTo
    {
        return $this->belongsTo(PackagePrice::class);
    }

    public function getPriceAttribute()
    {
        if ($this->order->isNotRecurring()) {
            return $this->cycle_price;
        }

        return $this->cycle_price * $this->order->period_in_days;
    }
}
