<?php

use Livewire\Volt\Component;
use App\Models\Subscription;

new class extends Component
{
    public $subscription;

    public function mount(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    #[\Livewire\Attributes\On('subscription-updated')]
    public function subscriptionUpdated($subscription_id)
    {

    }
}

?>

<div class="card">
    <div class="card-header">
        <div>
            <div class="row align-items-center">
                <div class="col">
                    <div class="card-title">{{ priceIn($subscription->total(), $subscription->currency) }} / {{ daysToPeriod($subscription->frequency) }}</div>
                    <div class="card-subtitle">{{ $subscription->description }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="datagrid">
            <div class="datagrid-item">
                <div class="datagrid-title">{{ __('messages.description') }}</div>
                <div class="datagrid-content">{{ $subscription->description }}</div>
            </div>
            <div class="datagrid-item">
                <div class="datagrid-title">{{ __('messages.status') }}</div>
                <div class="datagrid-content">
                    @if($subscription->status == 'active')
                        <span class="badge bg-green-lt">Active</span>
                    @elseif(($subscription->status == "inactive"))
                        <span class="badge bg-danger-lt">Inactive</span>
                    @else
                        <span class="badge bg-warning-lt">{{ ucfirst($subscription->status)  }}</span>
                    @endif
                </div>
            </div>
            <div class="datagrid-item">
                <div class="datagrid-title">Cycle</div>
                <div class="datagrid-content">
                    {{ priceIn($subscription->total(), $subscription->currency) }} / {{ daysToPeriod($subscription->frequency) }}
                </div>
            </div>
            <div class="datagrid-item">
                <div class="datagrid-title">{{ __('messages.currency') }}</div>
                <div class="datagrid-content">
                    {{ $subscription->currency }}
                </div>
            </div>
            <div class="datagrid-item">
                <div class="datagrid-title">{{ __('messages.gateway') }}</div>
                <div class="datagrid-content">
                    @if($subscription->gatewayConfig)
                        {{ $subscription->gatewayConfig->display_name }}
                    @else
                        <span class="badge bg-secondary-lt">None</span>
                    @endif
                </div>
            </div>
            <div class="datagrid-item">
                <div class="datagrid-title">Subscription ID</div>
                <div class="datagrid-content">{{ $subscription->subscription_id ?? '-' }}</div>
            </div>
            <div class="datagrid-item">
                <div class="datagrid-title">{{ __('messages.user') }}</div>
                <div class="datagrid-content">@if($subscription->user) <a href="{{ route('admin.users.edit', $subscription->user->id) }}" wire:navigate>{{ $subscription->user->username }} ({{ $subscription->user->email }})</a> @else <span class="badge bg-secondary-lt">Guest</span> @endif</div>
            </div>
            @if($subscription->subscribable_type && $subscription->subscribable_id)
                <div class="datagrid-item">
                    <div class="datagrid-title">Subscribable Type</div>
                    <div class="datagrid-content">{{ $subscription->subscribable_type }}</div>
                </div>
                <div class="datagrid-item">
                    <div class="datagrid-title">Subscribable ID</div>
                    <div class="datagrid-content">{{ $subscription->subscribable_id }}</div>
                </div>
            @endif
            <div class="datagrid-item">
                <div class="datagrid-title">Activated At</div>
                <div class="datagrid-content">
                    {{ $subscription->activated_at ? $subscription->activated_at->toDateTimeString() :  '-' }}
                    {{ $subscription->activated_at ? '(' . $subscription->activated_at->diffForHumans() . ')' : '' }}
                </div>
            </div>
            <div class="datagrid-item">
                <div class="datagrid-title">Last Checked At</div>
                <div class="datagrid-content">
                    {{ $subscription->last_checked_at ? $subscription->last_checked_at->toDateTimeString() :  '-' }}
                    {{ $subscription->last_checked_at ? '(' . $subscription->last_checked_at->diffForHumans() . ')' : '' }}
                </div>
            </div>
            <div class="datagrid-item">
                <div class="datagrid-title">Next Billing At</div>
                <div class="datagrid-content">
                    {{ $subscription->next_billing_at ? $subscription->next_billing_at->toDateTimeString() :  '-' }}
                    {{ $subscription->next_billing_at ? '(' . $subscription->next_billing_at->diffForHumans() . ')' : '' }}
                </div>
            </div>
        </div>
    </div>
</div>



