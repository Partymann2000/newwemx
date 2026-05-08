<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentWebhook extends Model
{
    protected $table = 'payment_webhooks';

    protected $fillable = [
        'payment_id',
        'ip_address',
        'message',
        'is_successful',
        'headers',
        'payload',
    ];

    protected $casts = [
        'is_successful' => 'boolean',
        'headers' => 'array',
        'payload' => 'array',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
