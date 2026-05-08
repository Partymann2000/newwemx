<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItemOption extends Model
{
    protected $table = 'cart_item_options';

    protected $fillable = [
        'cart_item_id',
        'name',
        'price',
        'key',
        'value',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:8',
            'data' => 'array',
        ];
    }

    public function item()
    {
        return $this->belongsTo(CartItem::class, 'cart_item_id');
    }

    /**
     * Option value safe for client-facing cart UI (keys containing "password" are redacted).
     */
    public function displayValueForCart(): string
    {
        if ($this->value === null || $this->value === '') {
            return '';
        }

        if (str_contains(strtolower((string) $this->key), 'password')) {
            return '********';
        }

        return (string) $this->value;
    }
}
