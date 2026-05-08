<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderSubscription extends Model
{
    protected $table = 'order_subscriptions';

    protected $fillable = [
        'order_id',
        'subscription_id',
        'remaining_days',
        'remaining_days_added',
    ];

    protected $casts = [
        'remaining_days_added' => 'boolean',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
