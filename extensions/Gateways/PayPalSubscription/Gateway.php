<?php

namespace Extensions\Gateways\PayPalSubscription;

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
    protected string $id = 'gateway-paypal-subscription';

    /**
     * Define the extension display name
     *
     * @var string
     */
    protected string $name = 'PayPal Subscription';

    /**
     * Define the extension description.
     *
     * @var string
     */
    protected string $description = 'Accept payments via PayPal for subscriptions.';

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
    public string $gatewayDescription = 'Setup a PayPal subscription for automatic recurring payments.';

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
                'label'       => 'PayPal Webhook ID (Leave empty to auto-create)',
                'description' => 'The ID of the PayPal webhook (auto-generated)',
                'type'        => 'text',
                'rules'       => ['nullable', 'string'],
            ],
        ];
    }

    protected Subscription $subscription;

    protected GatewayConfig $gatewayConfig;

    /**
     * Main entry point for creating a subscription and redirecting to PayPal.
     */
    public function subscribe(Subscription $subscription, GatewayConfig $gatewayConfig)
    {
        // define the subscription and gateway config for use in other methods
        $this->subscription = $subscription;
        $this->gatewayConfig = $gatewayConfig;

        return $this->createSubscription();
    }

    private function createSubscription()
    {
        // Ensure a webhook is in place for receiving PayPal events
        $this->createWebhookUrl();

        // If a PayPal plan ID is provided, use it; otherwise create a plan
        $planId = $this->subscription->data('paypal_plan_id') ?? $this->createPlan();

        if (!$planId) {
            throw new \Exception('Failed to create plan');
        }

        // Create the actual PayPal subscription
        $payPalSub = $this->createPaypalSubscription($planId);
        $this->subscription->update(['subscription_id' => $payPalSub['id']]);

        if (!isset($payPalSub['links'])) {
            throw new \Exception('Failed to create subscription');
        }

        // Redirect user to the "approve" link
        foreach ($payPalSub['links'] as $link) {
            if ($link['rel'] === 'approve') {
                return redirect($link['href']);
            }
        }

        throw new \Exception('Approval link not found');
    }

    protected function createPlan()
    {
        $product = $this->createProduct();
        $interval = $this->getOptimalInterval($this->subscription->frequency);

        $plan = $this->paypalRequest('post', '/billing/plans', [
            "product_id" => $product['id'],
            "name"       => $this->subscription->description,
            "billing_cycles" => [
                [
                    "frequency" => [
                        "interval_unit"  => $interval['interval'],
                        "interval_count" => $interval['frequency'],
                    ],
                    "tenure_type"   => "REGULAR",
                    "sequence"      => 1,
                    "total_cycles"  => 0, // infinite
                    "pricing_scheme" => [
                        "fixed_price" => [
                            "value"         => $this->subscription->amount,
                            "currency_code" => $this->subscription->currency,
                        ]
                    ]
                ]
            ],
            "payment_preferences" => [
                "auto_bill_outstanding"      => true,
                "setup_fee_failure_action"   => "CONTINUE",
                "payment_failure_threshold"  => 3
            ],
        ]);

        return $plan['id'] ?? null;
    }

    protected function createPaypalSubscription($planId)
    {
        return $this->paypalRequest('post', '/billing/subscriptions', [
            "plan_id"   => $planId,
            "custom_id" => $this->subscription->id,
            "application_context" => [
                "return_url" => $this->subscription->callbackUrl(),
                "cancel_url" => $this->subscription->cancelUrl(),
            ]
        ]);
    }

    protected function createProduct()
    {
        return $this->paypalRequest('post', '/catalogs/products', [
            "name"     => $this->subscription->description,
            "type"     => "DIGITAL",
            "category" => "SOFTWARE",
        ]);
    }

    /**
     * Helps find an interval (DAY, WEEK, MONTH, YEAR) that evenly divides $days.
     */
    private function getOptimalInterval(int $days): array
    {
        $intervals = [
            365 => 'YEAR',
            30  => 'MONTH',
            7   => 'WEEK',
            1   => 'DAY',
        ];

        // Pick the largest interval that cleanly divides $days
        foreach ($intervals as $dayEquivalent => $label) {
            if ($days % $dayEquivalent === 0) {
                return [
                    'frequency' => $days / $dayEquivalent,
                    'interval'  => $label
                ];
            }
        }

        // Fallback (shouldn't usually happen unless you have weird intervals)
        return ['frequency' => 1, 'interval' => 'MONTH'];
    }

    /**
     * Checks if a webhook already exists. If not, creates it.
     */
    private function createWebhookUrl()
    {
        $gateway = $this->gatewayConfig;

        // If we already have a webhook ID stored, return it
        if ($gateway->config('webhook_id')) {
            return $gateway->config('webhook_id');
        }

        // Otherwise, create a new webhook
        $webhook = $this->paypalRequest('post', '/notifications/webhooks', [
            "url" => route('payments.gateway.webhook', ['webhook_id' => $this->gatewayConfig->webhook_id]),
            "event_types" => [
                ["name" => "BILLING.SUBSCRIPTION.ACTIVATED"],
                ["name" => "BILLING.SUBSCRIPTION.CANCELLED"],
                ["name" => "BILLING.SUBSCRIPTION.EXPIRED"],
                ["name" => "BILLING.SUBSCRIPTION.RE-ACTIVATED"],
                ["name" => "BILLING.SUBSCRIPTION.SUSPENDED"],
                ["name" => "PAYMENT.SALE.COMPLETED"],
            ],
        ]);

        $gateway->updateConfig(['webhook_id' => $webhook['id']]);

        return $webhook['id'];
    }

    /**
     * Get or cache an access token from PayPal for subsequent calls.
     */
    private function getAccessToken()
    {
        return Cache::remember('paypal_subscriptions_access_token-gateway-'. $this->gatewayConfig->id, 60, function () {
            $gateway      = $this->gatewayConfig;
            $clientId     = $gateway->config('client_id');
            $clientSecret = $gateway->config('client_secret');

            $response = Http::withBasicAuth($clientId, $clientSecret)
                ->asForm()
                ->post($this->apiEndpoint('/oauth2/token'), [
                    'grant_type' => 'client_credentials',
                ]);

            if ($response->failed()) {
                throw new \Exception('Failed to get access token');
            }

            return $response->json()['access_token'];
        });
    }

    private function isSandboxMode()
    {
        return $this->gatewayConfig->config('mode', 'sandbox') === 'sandbox';
    }

    private function apiUrl()
    {
        return $this->isSandboxMode()
            ? 'https://api-m.sandbox.paypal.com/v1'
            : 'https://api-m.paypal.com/v1';
    }

    private function apiEndpoint($path = '')
    {
        return $this->apiUrl() . $path;
    }

    /**
     * Generic helper to send a request to PayPal and throw if it fails.
     */
    private function paypalRequest(string $method, string $path, array $data = [])
    {
        $response = Http::withToken($this->getAccessToken())->$method($this->apiEndpoint($path), $data);

        if ($response->failed()) {
            throw new \Exception(
                "PayPal $method $path failed: " . $response->body()
            );
        }

        return $response->json();
    }

    public function checkSubscription(Subscription $subscription, GatewayConfig $gatewayConfig)
    {
        $this->subscription = $subscription;
        $this->gatewayConfig = $gatewayConfig;

        $subData = $this->paypalRequest('get', '/billing/subscriptions/' . $subscription->subscription_id);

        if (!isset($subData['status'])) {
            throw new \Exception('Failed to check subscription');
        }

        // if subscription is cancelled, but still within active period, mark it as cancelled but active
        if ($subData['status'] === 'CANCELLED' && $subscription->isActive()) {
            // check if subscription is still within active period
            if ($subscription->next_billing_at && $subscription->next_billing_at->isFuture()) {
                $subscription->cancelled();
                return true;
            }
        }

        // cancel the subscription if it's not active
        if ($subData['status'] !== 'ACTIVE' && $subscription->isActive()) {
            $subscription->inactive();
            return false;
        }

        // update next billing date if changed
        if (isset($subData['billing_info']['next_billing_time'])) {
            $nextBilling = \Carbon\Carbon::parse($subData['billing_info']['next_billing_time']);
            if ($subscription->next_billing_at != $nextBilling) {
                $subscription->updateNextBillingDate($nextBilling);
            }
        }

        return $subData['status'] === 'ACTIVE';
    }

    public function cancelSubscription(Subscription $subscription, GatewayConfig $gatewayConfig)
    {
        $this->subscription = $subscription;
        $this->gatewayConfig = $gatewayConfig;

        $response = $this->paypalRequest('post', '/billing/subscriptions/' . $subscription->subscription_id . '/cancel', [
            'reason' => 'User canceled subscription',
        ]);

        $subscription->cancelled();

        return true;
    }

    public function callback(Request $request, GatewayConfig $gatewayConfig)
    {
        $subscription = Subscription::where('token', $request->get('subscription_token'))->first();

        if (!$subscription) {
            throw new \Exception('Subscription not found');
        }

        $this->subscription = $subscription;
        $this->gatewayConfig = $gatewayConfig;
        $subData = $this->paypalRequest('get', '/billing/subscriptions/' . $subscription->subscription_id);

        if (($subData['status'] ?? null) === 'ACTIVE') {
            $subscription->activated(
                $subscription->subscription_id,
                $subData['billing_info']['next_billing_time'] ?? null,
                $subData
            );
        }

        return redirect($subscription->successUrl());
    }

    /**
     * Process PayPal's webhook events asynchronously.
     */
    public function webhook(Request $request, GatewayConfig $gatewayConfig)
    {
        $eventType  = $request->input('event_type');
        $resource   = $request->input('resource') ?? [];
        $subId      = $resource['id'] ?? null;

        if (!$request->has('event_type')) {
            throw new \Exception('Unexpected Payload');
        }

        $customId   = $resource['custom_id'] ?? null;
        $subscription = Subscription::find($customId);

        if(!$subscription) {
            throw new \Exception('Subscription not found');
        }

        $this->subscription = $subscription;
        $this->gatewayConfig = $gatewayConfig;

        // Verify PayPal's signature
        $this->verifyPaypalWebhook($request);

        // Handle relevant subscription events
        if (in_array($eventType, [
            'BILLING.SUBSCRIPTION.ACTIVATED',
            'BILLING.SUBSCRIPTION.CANCELLED',
            'BILLING.SUBSCRIPTION.EXPIRED',
        ])) {
            if ($customId && $eventType === 'BILLING.SUBSCRIPTION.ACTIVATED') {
                if($subscription->isActive()) {
                    return;
                }

                if ($subscription) {
                    $subscription->activated(
                        $subId,
                        $resource['billing_info']['next_billing_time'] ?? null,
                        $resource
                    );
                }
            } elseif ($customId && $eventType === 'BILLING.SUBSCRIPTION.CANCELLED') {
                // Handle your cancellation logic
            }
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Verify PayPal's webhook signature to ensure authenticity.
     */
    private function verifyPaypalWebhook(Request $request)
    {
        $webhookId  = $this->createWebhookUrl(); // ensures we have a valid Webhook ID

        $requestData = $request->json()->all();

        // unset cart
        unset($requestData['cart']);

        $verification = $this->paypalRequest('post', '/notifications/verify-webhook-signature', [
            'auth_algo'         => $request->header('PAYPAL-AUTH-ALGO'),
            'cert_url'          => $request->header('PAYPAL-CERT-URL'),
            'transmission_id'   => $request->header('PAYPAL-TRANSMISSION-ID'),
            'transmission_sig'  => $request->header('PAYPAL-TRANSMISSION-SIG'),
            'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
            'webhook_id'        => $webhookId,
            'webhook_event'     => $requestData,
        ]);

        if (($verification['verification_status'] ?? null) !== 'SUCCESS') {
            throw new \Exception("Failed to verify PayPal webhook, {$verification['verification_status']} WI: {$webhookId} ". json_encode($verification));
        }
    }
}
