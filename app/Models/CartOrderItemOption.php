<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartOrderItemOption extends Model
{
    protected $table = 'cart_order_item_options';

    protected $fillable = [
        'cart_order_item_id',
        'name',
        'price',
        'key',
        'value',
        'data',
    ];

    protected $casts = [
        'price' => 'decimal:8',
        'data' => 'array',
    ];

    public function cartOrderItem()
    {
        return $this->belongsTo(CartOrderItem::class, 'cart_order_item_id');
    }
}
