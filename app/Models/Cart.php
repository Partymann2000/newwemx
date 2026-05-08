<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $table = 'carts';

    protected $fillable = [
        'session_id',
        'user_id',
    ];

    public static function actions(): \App\Actions\CartActions
    {
        return new \App\Actions\CartActions();
    }

    public function items()
    {
        return $this->hasMany(CartItem::class, 'cart_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function total()
    {
        $total = 0;

        foreach($this->items as $item) {
            $total += $item->totalWithOptions();
        }

        return $total;
    }

    public function getTotalQuantity()
    {
        // return the sum of all quantities
        return $this->items->sum('quantity');
    }

    public function clear()
    {
        // Clear all items in the cart
        $this->items()->delete();
    }
}
