<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartOrderItem extends Model
{
    protected $table = 'cart_order_items';

    protected $fillable = [
        'user_id',
        'cartable_type',
        'cartable_id',
        'basket_identifier',
        'name',
        'icon',
        'price',
        'quantity',
        'handler',
        'data',
        'is_paid',
    ];

    protected $casts = [
        'price' => 'decimal:8',
        'data' => 'array',
        'is_paid' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cartable()
    {
        return $this->morphTo();
    }

    public function options()
    {
        return $this->hasMany(CartOrderItemOption::class, 'cart_order_item_id');
    }

    public function completed(): void
    {
        $handler = $this->handler ?? null;

        try {
            if ($handler && class_exists($handler)) {
                $handler = new $handler;
                $handler->handle($this);
            }
        } catch (\Exception $e) {
            // provision of the item failed
        }
    }
}
