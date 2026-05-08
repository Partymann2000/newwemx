<?php

use App\Handlers\CartCompletedHandler;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Subscription;
use Livewire\Volt\Component;

new class extends Component
{
    public int $limit = 5;

    /**
     * Internal fetch window per event source before global merge.
     */
    public int $sourceFetchLimit = 50;

    #[\Livewire\Attributes\Computed]
    public function timelineEvents()
    {
        $orderEvents = Order::query()
            ->with(['user', 'package'])
            ->latest('created_at')
            ->limit($this->sourceFetchLimit)
            ->get()
            ->map(function ($order) {
                return [
                    'type' => 'order_placed',
                    'timestamp' => $order->created_at,
                    'title' => 'Order placed',
                    'description' => "Order #{$order->id} was placed for {$order->package?->name}.",
                    'links' => [
                        [
                            'label' => 'View Order',
                            'url' => route('admin.orders.edit', ['order' => $order->id]),
                        ],
                    ],
                    'meta' => [
                        'order_id' => $order->id,
                        'amount' => price($order->price),
                        'cycle' => $order->cycle(),
                        'status' => ucfirst($order->status),
                    ],
                    'user' => $order->user,
                ];
            });

        $paidPayments = Payment::query()
            ->with(['user', 'gatewayConfig'])
            ->where('status', 'paid')
            ->where('handler', '!=', CartCompletedHandler::class)
            ->latest('paid_at')
            ->limit($this->sourceFetchLimit)
            ->get()
            ->map(function ($payment) {
                return [
                    'type' => 'payment_paid',
                    'timestamp' => $payment->paid_at ?: $payment->updated_at ?: $payment->created_at,
                    'title' => 'Payment paid',
                    'description' => "Payment #{$payment->id} was marked as paid.",
                    'links' => [
                        [
                            'label' => 'View Payment',
                            'url' => route('admin.payments.edit', ['payment' => $payment->id]),
                        ],
                    ],
                    'meta' => [
                        'payment_id' => $payment->id,
                        'amount' => priceIn($payment->total(), $payment->currency),
                        'gateway' => $payment->gatewayConfig?->display_name ?: 'N/A',
                        'transaction_id' => $payment->transaction_id ?: 'N/A',
                    ],
                    'user' => $payment->user,
                ];
            });

        $checkoutCompleted = Payment::query()
            ->with(['user', 'gatewayConfig'])
            ->where('status', 'paid')
            ->where('handler', CartCompletedHandler::class)
            ->latest('paid_at')
            ->limit($this->sourceFetchLimit)
            ->get()
            ->map(function ($payment) {
                return [
                    'type' => 'checkout_completed',
                    'timestamp' => $payment->paid_at ?: $payment->updated_at ?: $payment->created_at,
                    'title' => 'Checkout completed',
                    'description' => "Checkout completed for cart payment #{$payment->id}.",
                    'links' => [
                        [
                            'label' => 'View Payment',
                            'url' => route('admin.payments.edit', ['payment' => $payment->id]),
                        ],
                    ],
                    'meta' => [
                        'payment_id' => $payment->id,
                        'cart_id' => data_get($payment->data, 'cart_id') ?: 'N/A',
                        'amount' => priceIn($payment->total(), $payment->currency),
                        'gateway' => $payment->gatewayConfig?->display_name ?: 'N/A',
                    ],
                    'user' => $payment->user,
                ];
            });

        $subscriptionsCreated = Subscription::query()
            ->with(['user', 'gatewayConfig'])
            ->latest('created_at')
            ->limit($this->sourceFetchLimit)
            ->get()
            ->map(function ($subscription) {
                return [
                    'type' => 'subscription_created',
                    'timestamp' => $subscription->created_at,
                    'title' => 'Subscription created',
                    'description' => "Subscription #{$subscription->id} was created.",
                    'links' => [
                        [
                            'label' => 'View Subscription',
                            'url' => route('admin.subscriptions.edit', ['subscription' => $subscription->id]),
                        ],
                    ],
                    'meta' => [
                        'subscription_id' => $subscription->subscription_id ?: 'N/A',
                        'amount' => priceIn($subscription->total(), $subscription->currency) . ' / ' . daysToPeriod($subscription->frequency),
                        'gateway' => $subscription->gatewayConfig?->display_name ?: 'N/A',
                        'status' => ucfirst($subscription->status),
                    ],
                    'user' => $subscription->user,
                ];
            });

        $subscriptionsCancelled = Subscription::query()
            ->with(['user', 'gatewayConfig'])
            ->where('status', 'cancelled')
            ->whereNotNull('cancelled_at')
            ->latest('cancelled_at')
            ->limit($this->sourceFetchLimit)
            ->get()
            ->map(function ($subscription) {
                return [
                    'type' => 'subscription_cancelled',
                    'timestamp' => $subscription->cancelled_at ?: $subscription->updated_at ?: $subscription->created_at,
                    'title' => 'Subscription cancelled',
                    'description' => "Subscription #{$subscription->id} was cancelled.",
                    'links' => [
                        [
                            'label' => 'View Subscription',
                            'url' => route('admin.subscriptions.edit', ['subscription' => $subscription->id]),
                        ],
                    ],
                    'meta' => [
                        'subscription_id' => $subscription->subscription_id ?: 'N/A',
                        'amount' => priceIn($subscription->total(), $subscription->currency) . ' / ' . daysToPeriod($subscription->frequency),
                        'gateway' => $subscription->gatewayConfig?->display_name ?: 'N/A',
                        'reason' => $subscription->cancel_reason ?: 'No reason provided',
                    ],
                    'user' => $subscription->user,
                ];
            });

        return $orderEvents
            ->concat($paidPayments)
            ->concat($checkoutCompleted)
            ->concat($subscriptionsCreated)
            ->concat($subscriptionsCancelled)
            ->sortByDesc('timestamp')
            ->take(5)
            ->values();
    }

    public function eventBadgeClass(string $type): string
    {
        return match ($type) {
            'order_placed' => 'bg-blue-lt',
            'payment_paid' => 'bg-green-lt',
            'checkout_completed' => 'bg-purple-lt',
            'subscription_created' => 'bg-azure-lt',
            'subscription_cancelled' => 'bg-red-lt',
            default => 'bg-secondary-lt',
        };
    }
}

?>

<div class="card mb-3" wire:poll.10s>
    <div class="card-header">
        <h3 class="card-title">Live Activity Timeline</h3>
        <div class="card-actions text-secondary">Auto-refreshes every 10s</div>
    </div>
    <div class="card-body">
        <ul class="timeline timeline-simple">
            @forelse($this->timelineEvents as $event)
                <li class="timeline-event">
                    <div class="timeline-event-icon {{ $this->eventBadgeClass($event['type']) }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-1" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M12 8l0 4l2 2"/>
                            <path d="M12 3a9 9 0 1 0 9 9"/>
                        </svg>
                    </div>
                    <div class="card timeline-event-card">
                        <div class="card-body">
                            <div class="text-secondary float-end">
                                {{ $event['timestamp']?->diffForHumans() }} ({{ $event['timestamp']?->format(settings('date_format', 'd M Y H:i')) }})
                            </div>
                            <h4 class="mb-1">{{ $event['title'] }}</h4>
                            <p class="text-secondary mb-2">{{ $event['description'] }}</p>

                            <div class="mb-2">
                                @foreach($event['meta'] as $key => $value)
                                    <span class="badge bg-secondary-lt me-1 mb-1">{{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $value }}</span>
                                @endforeach
                            </div>

                            @if(!empty($event['links']))
                                <div class="mb-2">
                                    @foreach($event['links'] as $link)
                                        <a href="{{ $link['url'] }}" wire:navigate class="me-2">{{ $link['label'] }}</a>
                                    @endforeach
                                </div>
                            @endif

                            <div class="d-flex align-items-center">
                                @if($event['user'])
                                    <span class="avatar avatar-xs me-2 rounded" style="background-image: url({{ $event['user']->getAvatarUrl() }})"></span>
                                    <span class="text-secondary">
                                        <a href="{{ route('admin.users.edit', ['user' => $event['user']->id]) }}" wire:navigate>
                                            {{ $event['user']->username }} ({{ $event['user']->email }})
                                        </a>
                                    </span>
                                @else
                                    <span class="text-secondary">Guest / Unknown user</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </li>
            @empty
                <li class="text-secondary">No recent activity found.</li>
            @endforelse
        </ul>
    </div>
</div>
