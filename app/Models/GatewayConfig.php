<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GatewayConfig extends Model
{
    protected $table = 'gateway_configs';

    protected $fillable = [
        'extension_identifier',
        'webhook_id',
        'display_name',
        'display_description',
        'icon',
        'type',
        'namespace',
        'config',
        'is_active',
        'is_staff_only',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'encrypted:array',
            'is_active' => 'boolean',
            'is_staff_only' => 'boolean',
        ];
    }

    public function gateway()
    {
        return $this->belongsTo(Gateway::class, 'extension_identifier', 'identifier');
    }

    public static function balanceGateway()
    {
        return GatewayConfig::where('extension_identifier', 'gateway-balance')->where('is_active', true)->where('is_staff_only', false)->first();
    }

    public static function actions(): \App\Actions\GatewayActions
    {
        return new \App\Actions\GatewayActions();
    }

    public function config($key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    public function updateConfig($data = [])
    {
        $this->config = array_merge($this->config ?? [], $data);
        return $this->save();
    }

    public function supportsCurrency($currency): bool
    {
        return $this->gateway->supportsCurrency($currency);
    }

    public function baseCurrency(): string
    {
        return $this->gateway->baseCurrency();
    }

    public function gatewayDisplayDescription()
    {
        return $this->gateway->gatewayDisplayDescription();
    }

    public function getGatewayIcon()
    {
        return $this->gateway->getGatewayIcon();
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('display_name', 'like', "%$search%");
    }
}
