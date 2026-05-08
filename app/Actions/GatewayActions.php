<?php

namespace App\Actions;

use App\Models\Gateway;
use App\Models\GatewayConfig;
use App\Support\LicensePlanLimits;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class GatewayActions extends Action
{
    public static function createGatewayConfigAsAdmin(array $input)
    {
        $validatedData = Validator::make($input, [
            'extension_identifier' => ['required', 'string', 'exists:extensions,identifier'],
            'webhook_id' => ['nullable', 'string', 'unique:gateway_configs,webhook_id'],
            'display_name' => ['required', 'string'],
            'display_description' => ['required', 'string'],
            'icon' => ['nullable', 'string'],
            'config' => ['nullable', 'array'],
            'is_enabled' => ['nullable', 'boolean'],
            'is_staff_only' => ['nullable', 'boolean'],
        ])->validate();

        $gateway = Gateway::where('identifier', $validatedData['extension_identifier'])->first();

        if (! $gateway) {
            throw ValidationException::withMessages([
                'extension_identifier' => 'Gateway not found',
            ]);
        }

        if ($validatedData['extension_identifier'] == 'gateway-balance' && GatewayConfig::balanceGateway()) {
            throw ValidationException::withMessages([
                'display_name' => 'You can only have one Balance Gateway configured.',
            ]);
        }

        LicensePlanLimits::assertCanCreateGatewayConfigs(1, 'display_name');

        // map config as $key => $rules
        $rules = $gateway->getConfigRules();
        $validatedConfig = Validator::make($validatedData['config'], $rules)->validate();

        return GatewayConfig::create(self::omitNullValues([
            'extension_identifier' => $validatedData['extension_identifier'],
            'webhook_id' => $validatedData['webhook_id'],
            'namespace' => $gateway->namespace,
            'type' => $gateway->getGatewayType(),
            'display_name' => $validatedData['display_name'],
            'display_description' => $validatedData['display_description'],
            'icon' => $validatedData['icon'] ?? null,
            'config' => $validatedConfig,
            'is_active' => $validatedData['is_enabled'] ?? null,
            'is_staff_only' => $validatedData['is_staff_only'] ?? false,
        ]));
    }

    public static function updateGatewayConfigAsAdmin(array $input)
    {
        $validatedData = Validator::make($input, [
            'gateway_id' => ['required', 'exists:gateway_configs,id'],
            'display_name' => ['sometimes', 'required', 'string'],
            'display_description' => ['sometimes', 'required', 'string'],
            'icon' => ['nullable', 'string'],
            'config' => ['nullable', 'array'],
            'is_enabled' => ['nullable', 'boolean'],
            'is_staff_only' => ['nullable', 'boolean'],
        ])->validate();

        $gatewayConfig = GatewayConfig::find($validatedData['gateway_id']);

        if (! $gatewayConfig) {
            throw ValidationException::withMessages([
                'gateway_id' => 'Gateway configuration not found',
            ]);
        }

        $validatedConfig = null;
        if (array_key_exists('config', $validatedData)) {
            $rules = $gatewayConfig->gateway->getConfigRules();
            $validatedConfig = Validator::make($validatedData['config'] ?? [], $rules)->validate();
        }

        return $gatewayConfig->update(self::omitNullValues([
            'display_name' => $validatedData['display_name'] ?? null,
            'display_description' => $validatedData['display_description'] ?? null,
            'icon' => $validatedData['icon'] ?? $gatewayConfig->icon,
            'config' => $validatedConfig,
            'is_active' => $validatedData['is_enabled'] ?? $gatewayConfig->is_active,
            'is_staff_only' => $validatedData['is_staff_only'] ?? $gatewayConfig->is_staff_only,
        ]));
    }
}
