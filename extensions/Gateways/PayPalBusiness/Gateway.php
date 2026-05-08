<?php

namespace Extensions\Gateways\PayPalBusiness;

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
    protected string $id = 'gateway-paypal-business';

    /**
     * Define the extension display name
     *
     * @var string
     */
    protected string $name = 'PayPal Business Gateway';

    /**
     * Define the extension description.
     *
     * @var string
     */
    protected string $description = 'PayPal Gateway for WemX';

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
            'client_id' => [
                'label'       => 'PayPal Client ID',
                'description' => 'Your PayPal REST API Client ID',
                'type'        => 'text',
                'rules'       => ['required', 'string'],
            ],
            'client_secret' => [
                'label'       => 'PayPal Client Secret',
                'description' => 'Your PayPal REST API Client Secret',
                'type'        => 'text',
                'rules'       => ['required', 'string'],
            ],
            'webhook_id' => [
                'label'       => 'PayPal Webhook ID',
                'description' => 'The Webhook ID from your PayPal app (needed to verify signatures)',
                'type'        => 'text',
                'rules'       => ['required', 'string'],
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
        $this->gateway = $gatewayConfig;
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)
            ->post($this->paypalApiUrl('checkout/orders'), [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => $payment->id,
                        'amount' => [
                            'currency_code' => $payment->currency,
                            'value' => $payment->total(),
                        ],
                    ],
                ],
                'application_context' => [
                    'cancel_url' => $payment->cancelUrl(),
                    'return_url' => $payment->callbackUrl(),
                    'shipping_preference'  => 'NO_SHIPPING',
                ],
            ]);

        if ($response->failed()) {
            throw new Exception('Failed to create PayPal order');
        }

        // store the transaction ID in the payment
        $payment->update([
            'transaction_id' => $response->json('id'),
            'gateway_data' => $response->json(),
        ]);

        $links = $response->json('links', []);

        // Find the approve link
        $approveLink = collect($links)->firstWhere('rel', 'approve')['href'] ?? null;

        if (!$approveLink) {
            throw new Exception("No approve link found in PayPal response.");
        }

        return redirect($approveLink);
    }

    private function getAccessToken()
    {
        return Cache::remember("paypal:paypal_access_token-{$this->gateway->id}", now()->addMinutes(15), function () {

            $clientId = $this->gateway->config('client_id');
            $clientSecret = $this->gateway->config('client_secret');

            $apiUrl = $this->isLive()
                ? 'https://api.paypal.com/v1/oauth2/token'
                : 'https://api.sandbox.paypal.com/v1/oauth2/token';

            $response = Http::withBasicAuth($clientId, $clientSecret)
                ->asForm()
                ->post($apiUrl, [
                    'grant_type' => 'client_credentials',
                ]);

            if ($response->failed()) {
                throw new Exception('Failed to get PayPal access token');
            }

            return $response->json('access_token');
        });
    }

    private function isLive()
    {
        return $this->gateway->config('mode') === 'live';
    }

    public function paypalApiUrl($path)
    {
        return $this->isLive()
            ? "https://api.paypal.com/v2/{$path}"
            : "https://api.sandbox.paypal.com/v2/{$path}";
    }

    /**
     * Handle the PayPal callback after payment approval.
     * This method captures the order and completes the payment.
     *
     * If something goes wrong, you can throw an exception to return a 500 error.
     * Do not include any sensitive in the exception message.
     *
     * Redirect the user to the success URL when the payment is successful.
     * To retrieve the success URL, you can use the `successUrl()` method on the Payment model.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\GatewayConfig $gatewayConfig
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Exception
     */
    public function callback(Request $request, GatewayConfig $gatewayConfig)
    {
        $payment = Payment::where('transaction_id', $request->get('token'))->first();

        if (!$payment) {
            throw new \Exception("No matching Payment found for transaction_id={$request->get('token')}.");
        }

        if($payment->isPaid()) {
            return redirect($payment->successUrl());
        }

        $this->gateway = $gatewayConfig;

        // PayPal sends back a "token" parameter which is actually the Order ID.
        // For v2/checkout/orders, it's typically named "token".
        // Double-check the actual parameter PayPal returns (some docs show 'token', others might show 'orderID').
        $orderId = $request->get('token');

        if (!$orderId) {
            throw new \Exception("No order 'token' (ID) provided in callback.");
        }

        // Retrieve (and possibly re-cache) the access token
        $accessToken = $this->getAccessToken();

        // Capture the order
        $captureResponse = Http::withToken($accessToken, 'Bearer')
            ->post($this->paypalApiUrl("checkout/orders/{$orderId}/capture"), ['success' => true]);

        if ($captureResponse->failed()) {
            throw new \Exception('Failed to capture PayPal order.');
        }

        $responseData = $captureResponse->json();

        // The status is often at the top-level: 'COMPLETED' or 'APPROVED'
        if (!isset($responseData['status'])) {
            throw new \Exception('Unexpected PayPal response structure — no status field found.');
        }

        // You may want to check for 'COMPLETED' or 'APPROVED'.
        // For an intent of CAPTURE, 'COMPLETED' means the funds are captured.
        if ($responseData['status'] !== 'COMPLETED') {
            // If it's 'APPROVED', you might still need to capture again or handle partial captures.
            // But typically, for CAPTURE, we want 'COMPLETED'.
            throw new \Exception("PayPal order not completed. Current status: {$responseData['status']}");
        }

        if (!$payment) {
            throw new \Exception("No matching Payment found for transaction_id={$orderId}.");
        }

        $payment->completed($orderId, $responseData);

        return redirect($payment->successUrl());
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
        $this->gateway = $gatewayConfig;

        // --- 1) Verify signature ---
        $webhookId = $this->gateway->config('webhook_id');

        if (!$webhookId) {
            throw new \Exception('Missing PayPal webhook_id in gateway config.');
        }

        $transmissionId  = $request->header('PAYPAL-TRANSMISSION-ID');
        $transmissionTime= $request->header('PAYPAL-TRANSMISSION-TIME');
        $certUrl         = $request->header('PAYPAL-CERT-URL');
        $authAlgo        = $request->header('PAYPAL-AUTH-ALGO');
        $transmissionSig = $request->header('PAYPAL-TRANSMISSION-SIG');
        $rawBody         = $request->getContent();

        if (!$transmissionId || !$transmissionTime || !$certUrl || !$authAlgo || !$transmissionSig || !$rawBody) {
            throw new \Exception('Missing required PayPal webhook headers/body.');
        }

        $accessToken = $this->getAccessToken();
        $verifyUrl = $this->isLive()
            ? 'https://api.paypal.com/v1/notifications/verify-webhook-signature'
            : 'https://api.sandbox.paypal.com/v1/notifications/verify-webhook-signature';

        $verifyResponse = Http::withToken($accessToken)
            ->post($verifyUrl, [
                'transmission_id'  => $transmissionId,
                'transmission_time'=> $transmissionTime,
                'cert_url'         => $certUrl,
                'auth_algo'        => $authAlgo,
                'transmission_sig' => $transmissionSig,
                'webhook_id'       => $webhookId,
                'webhook_event'    => json_decode($rawBody, true),
            ]);

        if ($verifyResponse->failed() || ($verifyResponse->json('verification_status') !== 'SUCCESS')) {
            throw new \Exception('Invalid PayPal webhook signature.');
        }

        // --- 2) Process event ---
        $event     = $request->json()->all();
        $eventType = $event['event_type'] ?? null;
        $resource  = $event['resource'] ?? [];

        // Helper to safely complete a payment if not already done
        $completePayment = function (string $orderId, array $payload) {
            $payment = Payment::where('transaction_id', $orderId)->first();
            if (!$payment) {
                // Not our payment — acknowledge quietly
                return;
            }
            if (!$payment->isPaid()) {
                $payment->completed($orderId, $payload);
            }
        };

        switch ($eventType) {
            case 'CHECKOUT.ORDER.APPROVED': {
                // Resource.id is the PayPal Order ID
                $orderId = $resource['id'] ?? null;
                if (!$orderId) {
                    break;
                }

                // Idempotency: if we already completed, skip capture
                $payment = Payment::where('transaction_id', $orderId)->first();
                if ($payment && $payment->isPaid()) {
                    break;
                }

                // --- 3) Capture the order ---
                $captureResponse = Http::withToken($accessToken, 'Bearer')
                    ->post($this->paypalApiUrl("checkout/orders/{$orderId}/capture"), []);

                if ($captureResponse->failed()) {
                    throw new \Exception('Failed to capture PayPal order from webhook.');
                }

                $captureData = $captureResponse->json();
                if (($captureData['status'] ?? null) === 'COMPLETED') {
                    $completePayment($orderId, $captureData);
                } else {
                    throw new \Exception("PayPal order capture did not complete. Status: " . ($captureData['status'] ?? 'unknown'));
                }
                break;
            }

            case 'PAYMENT.CAPTURE.COMPLETED': {
                // Try to resolve back to the order id
                $orderId = $resource['supplementary_data']['related_ids']['order_id'] ?? null;

                // Fallback: sometimes the order link is in resource.links -> parse it
                if (!$orderId && !empty($resource['links'])) {
                    foreach ($resource['links'] as $link) {
                        if (!empty($link['href']) && strpos($link['href'], '/v2/checkout/orders/') !== false) {
                            $path = parse_url($link['href'], PHP_URL_PATH);
                            if ($path && str_contains($path, '/orders/')) {
                                $orderId = basename($path);
                                break;
                            }
                        }
                    }
                }

                if ($orderId) {
                    $completePayment($orderId, $event);
                }
                break;
            }

            default:
                // Other events can be acknowledged without action
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Webhook received successfully',
        ]);
    }
}
