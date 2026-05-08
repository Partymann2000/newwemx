<?php

namespace App\Extensions\Foundation;

use App\Models\GatewayConfig;
use Illuminate\Http\Request;
use App\Extensions\Traits\SubscriptionGatewayHelper;
use App\Models\Subscription;

abstract class SubscriptionGatewayExtension extends GatewayFoundation
{
    use SubscriptionGatewayHelper;

    /**
     * Define the gateway type. Can be "subscription" or "payment".
     *
     * @var string
     */
    protected string $gatewayType = 'subscription';

    public function __construct()
    {
        parent::__construct();
    }

    abstract public function subscribe(Subscription $subscription, GatewayConfig $gatewayConfig);

    abstract public function checkSubscription(Subscription $subscription, GatewayConfig $gatewayConfig);

    abstract public function cancelSubscription(Subscription $subscription, GatewayConfig $gatewayConfig);
}
