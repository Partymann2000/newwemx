<?php

namespace Extensions\Gateways\StripeCheckout;

use App\Extensions\Foundation\GatewayExtension;
use Illuminate\Http\Request;
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
    protected string $id = 'gateway-stripe-checkout';

    /**
     * Define the extension display name
     *
     * @var string
     */
    protected string $name = 'Stripe Checkout';

    /**
     * Define the extension description.
     *
     * @var string
     */
    protected string $description = 'Accept payments online using Stripe Checkout.';

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
    public string $gatewayDescription = 'Pay easily using Strip\'s hosted checkout page.';

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
            'secret_key' => [
                'label'       => 'Stripe Secret Key',
                'description' => 'Your Stripe Secret API Key',
                'type'        => 'text',
                'rules'       => ['required', 'string', 'starts_with:sk_'],
            ],
            'webhook_secret' => [
                'label'       => 'Stripe Webhook Secret',
                'description' => 'Your Stripe Webhook Secret',
                'type'        => 'text',
                'rules'       => ['required', 'string', 'starts_with:whsec_'],
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
        $stripeSecretKey = $gatewayConfig->config('secret_key');

        $response = Http::withToken($stripeSecretKey)->asForm()
            ->post('https://api.stripe.com/v1/checkout/sessions', [
                'line_items' => [[
                    'price_data' => [
                        'currency' => $payment->currency,
                        'unit_amount' => $payment->total() * 100,
                        'product_data' => [
                            'name' => $payment->description,
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $payment->callbackUrl(),
                'cancel_url' => $payment->cancelUrl(),
                'metadata' => [
                    'payment_token' => $payment->token,
                ],
            ]);

        if ($response->failed()) {
            // Handle failure (e.g., log error, throw exception)
            throw new \Exception('Stripe Checkout Session creation failed: ' . $response->body());
        }

        $checkoutSession = $response->json();

        // store transaction id locally
        $payment->update([
            'transaction_id' => $checkoutSession['id'],
        ]);

        return redirect($checkoutSession['url']);
    }

    public function callback(Request $request, GatewayConfig $gatewayConfig)
    {
        // Handle the Stripe webhook event
        if($request->has('payment_token')) {
            $payment = Payment::where('token', $request->payment_token)->first();

            if (!$payment) {
                throw new Exception('Payment not found');
            }

            if($payment->isPaid()) {
                return redirect($payment->successUrl());
            }

            // make api call to stripe to get the payment status
            $stripeSecretKey = $gatewayConfig->config('secret_key');

            $response = Http::withToken($stripeSecretKey)->get('https://api.stripe.com/v1/checkout/sessions/' . $payment->transaction_id);

            if ($response->failed()) {
                // Handle failure (e.g., log error, throw exception)
                throw new \Exception('Stripe Checkout Session retrieval failed: ' . $response->body());
            }

            $checkoutSession = $response->json();

            // check if the payment token is the same as the one in the checkout session
            if($checkoutSession['metadata']['payment_token'] !== $payment->token) {
                throw new Exception('Payment token mismatch');
            }

            if($checkoutSession['payment_status'] === 'paid') {
                $payment->completed($checkoutSession['id'], $checkoutSession);
                $payment->logPaymentWebhook('Payment completed via callback');
            }

            return redirect($payment->successUrl());
        }

        return redirect('/');
    }

    /**
     * Handle (webhooks) from Stripe.
     * Stripe calls this endpoint whenever the payment status changes.
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
        // Get the stripe transaction id from the request
        $transactionId = $request->input('data.object.id');

        if(!$transactionId ) {
            throw new Exception('Transaction ID not found');
        }

        // find the payment by the transaction id
        $payment = Payment::where('transaction_id', $transactionId)->first();

        if(!$payment) {
            throw new Exception('Payment not found');
        }

        // verify the webhook
        $this->verifyStripeWebhook($gatewayConfig->config('webhook_secret'));

        if($payment->isPaid()) {
            return response()->json(['message' => 'Payment already completed'], 200);
        }

        // get the payment status from the request
        $paymentStatus = $request->input('data.object.payment_status');

        if($paymentStatus === 'paid') {
            $payment->completed($transactionId, $request->input('data.object'));

            return response()->json(['message' => 'Payment completed'], 200);
        }

        throw new Exception('Webhook does not contain valid data');
    }

    private function verifyStripeWebhook($webhookSecret)
    {
        // Get the raw request body
        $payload = file_get_contents('php://input');

        // Retrieve the Stripe signature header
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? null;

        if (!$sigHeader) {
            throw new \Exception("Stripe signature header is missing.");
        }

        // Parse the Stripe signature header
        $timestamp = null;
        $signature = null;
        foreach (explode(',', $sigHeader) as $part) {
            list($key, $value) = explode('=', trim($part), 2);
            if ($key === 't') {
                $timestamp = $value;
            } elseif ($key === 'v1') {
                $signature = $value;
            }
        }

        if (!$timestamp || !$signature) {
            throw new \Exception("Invalid Stripe signature header format.");
        }

        // Compute the expected signature
        $signedPayload = $timestamp . '.' . $payload;
        $expectedSignature = hash_hmac('sha256', $signedPayload, $webhookSecret);

        // Compare the computed signature with the Stripe-provided signature
        if (!hash_equals($expectedSignature, $signature)) {
            throw new \Exception("Invalid Stripe webhook signature.");
        }

        return json_decode($payload, true); // Return the webhook event as an array
    }
}
