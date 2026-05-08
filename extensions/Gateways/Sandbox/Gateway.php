<?php

namespace Extensions\Gateways\Sandbox;

use App\Extensions\Foundation\GatewayExtension;
use App\Models\GatewayConfig;
use App\Models\Payment;

class Gateway extends GatewayExtension
{
    /**
     * Define the extension identifier. This identifier should be unique.
     * For example, if the extension name is "Example Module", the extension identifier should be "module-example".
     *
     * @var string
     */
    protected string $id = 'gateway-sandbox';

    /**
     * Define the extension display name
     *
     * @var string
     */
    protected string $name = 'Sandbox Gateway';

    /**
     * Define the extension description.
     *
     * @var string
     */
    protected string $description = 'A sandbox payment gateway for testing purposes.';

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
    protected string $gatewayType = 'payment';

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
    public string $gatewayDescription = 'This is a sandbox payment gateway for testing purposes. No real money will be processed.';

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
     * Main function to initiate a payment.
     * This method creates a PayPal order and redirects the user to PayPal.
     */
    public function pay(Payment $payment, GatewayConfig $gatewayConfig)
    {
        $transactionId = 'TEST-' . strtoupper(uniqid());
        $payment->completed($transactionId);

        return redirect(route('payments.completed'));
    }
}
