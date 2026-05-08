<?php

namespace Extensions\Gateways\SandboxSubscription;

use App\Extensions\Foundation\SubscriptionGatewayExtension;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Models\GatewayConfig;
use Exception;

class Gateway extends SubscriptionGatewayExtension
{
    /**
     * Define the extension identifier. This identifier should be unique.
     * For example, if the extension name is "Example Module", the extension identifier should be "module-example".
     *
     * @var string
     */
    protected string $id = 'gateway-sandbox-subscription';

    /**
     * Define the extension display name
     *
     * @var string
     */
    protected string $name = 'Sandbox Subscription Gateway';

    /**
     * Define the extension description.
     *
     * @var string
     */
    protected string $description = 'Sandbox subscription gateway for testing recurring payments.';

    /**
     * Define the extension type. For example, if the extension is a module, the extension type should be "Module".
     *
     * @var string
     */
    protected string $type = 'Gateway';

    /**
     * Define the gateway type. Can be "subscription" or "payment".
     *
     * @var string
     */
    protected string $gatewayType = 'subscription';

    /**
     * Define the supported currencies.
     *
     * @var array
     */
    protected array $currencies = [];

    /**
     * Define the extension version.
     *
     * @var string
     */
    protected string $version = '1.0.0';

    /**
     * Define the WemX versions that the extension is compatible with.
     * Use * to define that the extension is compatible with all versions.
     *
     * @var array
     */
    protected array $wemxVersions = [
        '1.0.0',
    ];

    /**
     * Define the authors of the extension.
     *
     * @var array
     */
    protected array $authors = [
        [
            'name' => 'WemX',
            'email' => 'team@wemx.net',
        ]
    ];

    /**
     * Define the extension display description to customers.
     *
     * @var string
     */
    public string $gatewayDescription = 'A sandbox subscription gateway for testing recurring payments.';

    /**
     * Define the default configuration values required to setup this gateway
     * i.e host, api key, or other values. Use Laravel validation rules for
     *
     * Laravel validation rules: https://laravel.com/docs/10.x/validation
     *
     * @return array
     */
    public static function setConfig(): array
    {
        return [];
    }

    /**
     * Main entry point for creating a subscription and redirecting to PayPal.
     */
    public function subscribe(Subscription $subscription, GatewayConfig $gatewayConfig)
    {
        $subscription->activated('sandbox_' . uniqid());

        return redirect($subscription->successUrl());
    }

    public function checkSubscription(Subscription $subscription, GatewayConfig $gatewayConfig)
    {
        if ($subscription->isActive()) {
            if ($subscription->next_billing_at && $subscription->next_billing_at->isPast()) {
                $subscription->updateNextBillingDate(now()->addDays($subscription->frequency));
            }
        }

        return true;
    }

    public function cancelSubscription(Subscription $subscription, GatewayConfig $gatewayConfig)
    {
        $subscription->cancelled();
    }
}
