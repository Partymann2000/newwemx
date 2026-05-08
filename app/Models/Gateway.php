<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class Gateway extends Extension
{
    /**
     * The "booted" method of the model.
     *
     * This is where we add the global scope.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope('gateway', function (Builder $builder) {
            $builder->where('type', 'gateway');
        });
    }

    public static function getActiveGateways()
    {
        return Gateway::where('status', 'enabled')->get();
    }

    public function pay($payment, $gateway)
    {
        return $this->extension()->pay($payment, $gateway);
    }

    public function subscribe($subscription, $gateway)
    {
        return $this->extension()->subscribe($subscription, $gateway);
    }

    public function cancelSubscription(Subscription $subscription, $gateway)
    {
        return $this->extension()->cancelSubscription($subscription, $gateway);
    }

    public function checkSubscription(Subscription $subscription, $gateway)
    {
        return $this->extension()->checkSubscription($subscription, $gateway);
    }

    public function handleWebhook(Request $request, GatewayConfig $gatewayConfig)
    {
        return $this->extension()->webhook($request, $gatewayConfig);
    }

    public function handleCallback(Request $request, GatewayConfig $gatewayConfig)
    {
        return $this->extension()->callback($request, $gatewayConfig);
    }

    public function hasWebhook(): bool
    {
        return $this->extension()->hasWebhook();
    }

    public function hasCallback(): bool
    {
        return $this->extension()->hasCallback();
    }

    public function getConfig()
    {
        return collect($this->extension()->setConfig());
    }

    public function getConfigRules(string $prefix = ''): array
    {
        return $this->getConfig()->mapWithKeys(function ($config, $key) use ($prefix) {
            return [$prefix . $key => $config['rules']];
        })->toArray();
    }

    public function getGatewayType()
    {
        return $this->extension()->getGatewayType();
    }

    public function gatewayDisplayDescription()
    {
        return $this->functions()->getGatewayDescription();
    }

    public function getGatewayIcon()
    {
        return $this->functions()->getGatewayIcon();
    }

    public function getCurrencies(): array
    {
        return $this->functions()->getSupportedCurrencies();
    }

    public function supportsCurrency($currency): bool
    {
        $currencies = $this->getCurrencies();

        // if array is empty, assume all currencies are supported
        if (empty($currencies)) {
            return true;
        }

        return in_array($currency, $currencies);
    }

    public function baseCurrency()
    {
        return $this->functions()->getBaseCurrency();
    }

    public function supportsRefunds(): bool
    {
        return $this->functions()->supportsRefunds();
    }

    public function supportsPartialRefunds(): bool
    {
        return $this->functions()->supportsPartialRefunds();
    }
}
