<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BalanceTransaction extends Model
{
    protected $table = 'balance_transactions';

    protected $fillable = [
        'user_id',
        'result',
        'description',
        'amount',
        'balance_before_transaction',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'balance_before_transaction' => 'decimal:8',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
