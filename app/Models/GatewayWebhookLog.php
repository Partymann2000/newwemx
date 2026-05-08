<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GatewayWebhookLog extends Model
{
    protected $table = 'gateway_webhook_logs';

    protected $fillable = [
        'gateway_config_id',
        'ip_address',
        'message',
        'is_successful',
        'headers',
        'payload',
    ];

    protected $casts = [
        'headers' => 'array',
        'payload' => 'array',
        'is_successful' => 'boolean',
    ];

    public function gatewayConfig()
    {
        return $this->belongsTo(GatewayConfig::class);
    }
}
