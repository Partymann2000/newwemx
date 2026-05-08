@extends('theme::dashboard.dashboard-layout')

@section('container')
    <div class="mb-4">
        @livewire(client_view_path('livewire.table'), [
            'title' => 'Subscriptions',
            'description' => 'View your active and past subscriptions.',
            'columns' => [
                'Description',
                'Gateway',
                'Subscription ID',
                'Cycle',
                'Status',
                'Activated At',
                'Next Billing At',
                'Actions',
            ],
            'rows' =>
                auth()->user()->subscriptions()->latest()->whereNotIn('status', ['pending'])->get()->map(function($subscription) {
                    return [
                        Str::limit($subscription->description, 50),
                        $subscription->gatewayConfig ? $subscription->gatewayConfig->display_name : 'N/A',
                        Str::limit($subscription->subscription_id, 32),
                        PriceIn($subscription->amount, $subscription->currency) . ' / ' . daysToPeriod($subscription->frequency),
                        ucfirst($subscription->status) . ($subscription->cancelled_at ? ' (Cancelled on ' . $subscription->cancelled_at->format('Y-m-d') . ')' : ''),
                        $subscription->activated_at ? $subscription->activated_at->format('Y-m-d') : 'N/A',
                        $subscription->next_billing_at ? $subscription->next_billing_at->format('Y-m-d') : 'N/A',
                        ($subscription->manage_url
                            ? '<a href="'. $subscription->manage_url .'" target="_blank" class="text-primary-600 dark:text-primary-500 hover:underline">Manage</a> '
                            : ''
                        ) .
                        ($subscription->status == 'active'
                            ? '<a href="#" class="text-red-600 dark:text-red-500 hover:underline" data-drawer-target="cancel-subscription-drawer" data-drawer-show="cancel-subscription-drawer" data-drawer-placement="right" aria-controls="cancel-subscription-drawer">Cancel</a>'
                            : ''
                        )
                    ];
                })->toArray(),
        ])
    </div>

    @livewire(client_view_path('dashboard.livewire.cancel-subscription-drawer'))
@endsection
