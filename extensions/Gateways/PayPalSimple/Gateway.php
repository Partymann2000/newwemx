<?php

namespace Extensions\Gateways\PayPalSimple;

use App\Extensions\Foundation\GatewayExtension;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Models\GatewayConfig;
use App\Models\Payment;
use Exception;

class Gateway extends GatewayExtension
{
    /**
     * Define the extension identifier. This identifier should be unique.
     * For example, if the extension name is "Example Module", the extension identifier should be "module-example".
     *
     * @var string
     */
    protected string $id = 'gateway-paypal-simple';

    /**
     * Define the extension display name
     *
     * @var string
     */
    protected string $name = 'PayPal Simple Gateway';

    /**
     * Define the extension description.
     *
     * @var string
     */
    protected string $description = 'Simple integration requiring only a PayPal email address.';

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
     * Define the supported currencies.
     *
     * @var array
     */
    protected array $currencies = [
        'USD', // United States Dollar
        'EUR', // Euro
        'GBP', // Pound Sterling
        'AUD', // Australian Dollar
        'BRL', // Brazilian Real
        'CAD', // Canadian Dollar
        'CNY', // Chinese Renminbi
        'CZK', // Czech Koruna
        'DKK', // Danish Krone
        'HKD', // Hong Kong Dollar
        'HUF', // Hungarian Forint (zero-decimal)
        'ILS', // Israeli New Shekel
        'JPY', // Japanese Yen (zero-decimal)
        'MYR', // Malaysian Ringgit
        'MXN', // Mexican Peso
        'TWD', // New Taiwan Dollar (zero-decimal)
        'NZD', // New Zealand Dollar
        'NOK', // Norwegian Krone
        'PHP', // Philippine Peso
        'PLN', // Polish Złoty
        // 'RUB', // Russian Ruble, only works in Russia
        'SGD', // Singapore Dollar
        'SEK', // Swedish Krona
        'CHF', // Swiss Franc
        'THB', // Thai Baht
    ];

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
    public string $gatewayDescription = 'PayPal is a popular payment gateway that allows you to accept payments online.';

    /**
     * Define the extension display icon to customers.
     *
     * @var string
     */
    public string $gatewayIcon = 'https://flowbite.s3.amazonaws.com/blocks/e-commerce/brand-logos/paypal.svg';

    public $gateway;

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
        return [
            'mode' => [
                'label'       => 'PayPal Mode (Sandbox/Live)',
                'description' => 'Select sandbox for testing or live for production',
                'type'        => 'select',
                'options'     => ['sandbox' => 'Sandbox', 'live' => 'Live'],
                'rules'       => ['required'],
            ],
            'email' => [
                'label' => 'PayPal Email',
                'description' => 'The email address on which you receive payments',
                'type' => 'text',
                'rules' => ['required', 'email'],
            ],
        ];
    }


    /**
     * Handle the payment process.
     * This method should create a payment, then redirect the user to the payment gateway for approval.
     *
     * If something goes wrong, you can throw an exception to return a 500 error.
     * Do not include any sensitive in the exception message.
     *
     * To retrieve config values, you can use $gatewayConfig->config('key', 'default_value').
     *
     * @param \App\Models\Payment $payment
     * @param \App\Models\GatewayConfig $gatewayConfig
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Exception
     */
    public function pay(Payment $payment, GatewayConfig $gatewayConfig)
    {
        $url = $this->getPayPalUrl($gatewayConfig->config('mode', 'live'));

        $checkoutUrl = url()->query($url, [
            'cmd' => '_xclick',
            'business' => $gatewayConfig->config('email'),
            'item_name' => $payment->description,
            'item_number' => $payment->id,
            'amount' => $payment->total(),
            'currency_code' => $payment->currency,
            'cancel_return' => $payment->cancelUrl(),
            'notify_url' => $payment->webhookUrl(),
            'return' => $payment->callbackUrl(),
            'rm' => 2,
            'charset' => 'uft-8',
            'no_note' => 1,
        ]);

        return redirect($checkoutUrl);
    }

    private function getPayPalUrl(string $environment = 'live')
    {
        if ($environment == 'live') {
            return 'https://ipnpb.paypal.com/cgi-bin/webscr';
        }

        return 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';
    }

    public function callback(Request $request, GatewayConfig $gatewayConfig)
    {
        $payment = Payment::where('token', $request->input('payment_token'))->first();

        if (!$payment) {
            throw new Exception("Payment not found");
        }

        return redirect()->intended($payment->successUrl());
    }

    /**
     * Handle (webhooks) from PayPal.
     * PayPal calls this endpoint whenever the payment status changes.
     *
     * If something goes wrong, you can throw an exception to return a 500 error.
     * Do not include any sensitive in the exception message.
     *
     * Return a 200 json response to acknowledge the webhook.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\GatewayConfig $gatwewayConfig
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
     *
     * Example response:
     * return response()->json([
     *     'success' => true,
     *     'message' => 'Webhook received successfully',
     * ]);
     */
    public function webhook(Request $request, GatewayConfig $gatewayConfig)
    {
        $payment = Payment::where('token', $request->input('payment_token'))->first();

        if (!$payment) {
            throw new Exception("Payment not found");
        }

        $gateway = $gatewayConfig;

        // The IPN request is a POST request, so we'll get the data from the request input
        $ipnPayload = $request->post();

        // Before processing the IPN message, you should validate it to make sure it's actually from PayPal
        $this->validateIpn($ipnPayload, $gateway);

        // Perform some validation checks
        $this->validationChecks($ipnPayload, $payment);

        // Log the webhook if the payment is not already paid
        if ($payment->isNotPaid()) {
            $payment->logPaymentWebhook('IPN received and validated, processing payment', $request->ip(), $request->headers->all(), $ipnPayload);
        }

        $payment->completed($ipnPayload['txn_id'], $ipnPayload);

        return response()->json([
            'success' => true,
            'message' => 'Webhook received successfully',
        ]);
    }

    private function validateIpn($ipnPayload, $gateway)
    {
        $paypalUrl = $this->getPayPalUrl($gateway->config('mode', 'production'));

        // Build a form-data array, prepending cmd=_notify-validate
        $postData = array_merge(['cmd' => '_notify-validate'], $ipnPayload);

        // Make the request as application/x-www-form-urlencoded
        $response = Http::asForm()
            ->post($paypalUrl, $postData);

        // Compare to see if it is 'VERIFIED'
        if($response->body() !== 'VERIFIED') {
            throw new \Exception('IPN is not valid');
        }

        return true;
    }

    private function validationChecks($ipnPayload, $payment)
    {
        // check if the payment status is completed
        if ($ipnPayload['payment_status'] !== 'Completed') {
            // The payment status is not completed
            throw new Exception("The payment status is not completed");
        }

        // check if the payment is not already paid
        if ($payment->isPaid()) {
            // The payment is already paid
            throw new Exception("The payment {$payment->id} is already paid");
        }

        // compare the payment amount sent with the amount from the database
        if ($ipnPayload['mc_gross'] != $payment->total()) {
            // The payment amount doesn't match the amount from the database
            throw new Exception("The payment {$payment->id} amount doesn't match the amount from the database. Given amount: {$ipnPayload['mc_gross']}");
        }

        // check if the receiver email is the same as the one in the database
        if ($ipnPayload['receiver_email'] != $payment->gatewayConfig->config('email')) {
            // The receiver email doesn't match the email from the database
            throw new Exception("The receiver email doesn't match the email from the gateway config");
        }

        // check if the item_number is the same as the one in the database
        if ($ipnPayload['item_number'] != $payment->id) {
            // The item_number doesn't match the item_number from the database
            throw new Exception("The item_number doesn't match the item_number from the database, Given item_number: {$ipnPayload['item_number']}");
        }

        // check if the currency is the same as the one in the database
        if ($ipnPayload['mc_currency'] != $payment->currency) {
            // The currency doesn't match the currency from the database
            throw new Exception("The currency doesn't match the currency from the database, Given currency: {$ipnPayload['mc_currency']}");
        }

        // check if the transaction is already processed
        if (Payment::where('transaction_id', $ipnPayload['txn_id'])->exists()) {
            // The transaction is already processed
            throw new Exception("The transaction {$ipnPayload['txn_id']} is already processed");
        }
    }
}
