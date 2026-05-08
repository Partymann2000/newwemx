<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentRefund extends Model
{
    protected $table = 'payment_refunds';

    protected $fillable = [
        'payment_id',
        'user_id',
        'gateway_config_id',
        'transaction_id',
        'amount',
        'currency',
        'reason',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gatewayConfig()
    {
        return $this->belongsTo(GatewayConfig::class);
    }
}
