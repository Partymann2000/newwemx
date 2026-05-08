<?php

namespace App\Extensions\Foundation;

use App\Models\GatewayConfig;
use App\Extensions\Traits\GatewayHelper;
use App\Models\Payment;

abstract class GatewayExtension extends GatewayFoundation
{
    use GatewayHelper;

    /**
     * Define the gateway type. Can be "subscription" or "payment".
     *
     * @var string
     */
    protected string $gatewayType = 'payment';

    public function __construct()
    {
        parent::__construct();
    }

    abstract public function pay(Payment $payment, GatewayConfig $gatewayConfig);
}
