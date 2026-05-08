<?php

namespace Extensions\Gateways\Balance;

use App\Extensions\Foundation\GatewayExtension;
use App\Models\GatewayConfig;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;

class Gateway extends GatewayExtension
{
    /**
     * Define the extension identifier. This identifier should be unique.
     * For example, if the extension name is "Example Module", the extension identifier should be "module-example".
     */
    protected string $id = 'gateway-balance';

    /**
     * Define the extension display name
     */
    protected string $name = 'Balance Gateway';

    /**
     * Define the extension description.
     */
    protected string $description = 'Balance Gateway for WemX';

    /**
     * Define the extension type. For example, if the extension is a module, the extension type should be "Module".
     */
    protected string $type = 'Gateway';

    /**
     * Define the gateway type. Can be "subscription" or "payment".
     */
    protected string $gatewayType = 'payment';

    /**
     * Define the supported currencies.
     */
    protected array $currencies = [];

    public string $marketplace_id = '1';

    /**
     * Define the extension version.
     */
    protected string $version = '1.0.0';

    /**
     * Define the WemX versions that the extension is compatible with.
     * Use * to define that the extension is compatible with all versions.
     */
    protected array $wemxVersions = [
        '1.0.0',
    ];

    /**
     * Define the authors of the extension.
     */
    protected array $authors = [
        [
            'name' => 'WemX',
            'email' => 'team@wemx.net',
        ],
    ];

    /**
     * Define the extension display description to customers.
     */
    public string $gatewayDescription = 'Pay with your balance.';

    /**
     * Define the default configuration values required to setup this gateway
     * i.e host, api key, or other values. Use Laravel validation rules for
     *
     * Laravel validation rules: https://laravel.com/docs/10.x/validation
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
        if (! Auth::check() || ! $payment->user || Auth::id() !== $payment->user_id) {
            return redirect()
                ->route('payments.view', ['payment' => $payment->token])
                ->withErrors([
                    'gateway_config_id' => 'You must be signed in as the payment owner to pay with balance.',
                ]);
        }

        // User balances are stored in the application's base currency.
        $convertedAmount = price(
            $payment->total(),
            in: $payment->currency,
            to: baseCurrency(),
            absolute: true
        );

        if (($payment->user->balance ?? 0) < $convertedAmount) {
            return redirect()
                ->route('payments.view', ['payment' => $payment->token])
                ->withErrors([
                    'gateway_config_id' => 'Insufficient balance to complete this payment.',
                ]);
        }

        $payment->user->updateBalance('-', $convertedAmount, "Payment #{$payment->id} via balance gateway");

        $transactionId = 'BALANCE-'.strtoupper(uniqid());
        $payment->completed($transactionId);

        return redirect(route('payments.completed'));
    }

    public function refund($payment, $amount)
    {
        // the amount is in the payment currency, so no need to convert it
        // to the applications default currency
        $convertedAmount = price($amount, in: $payment->currency, to: baseCurrency(), absolute: true);

        $description = "Refund for payment #{$payment->id}";
        $payment->user->updateBalance('+', $convertedAmount, $description);
    }
}
