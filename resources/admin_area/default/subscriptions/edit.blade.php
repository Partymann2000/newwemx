@extends('admin::layouts.wrapper', [
    'activePage' => 'subscriptions',
])

@section('title',  'Subscription #' . $subscription->id)

@section('content')
    <div class="row">
        <div class="col-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">{{ priceIn($subscription->total(), $subscription->currency) }} / {{ daysToPeriod($subscription->frequency) }}</h3>
                    <div class="card-actions">
                        @if($subscription->status == 'active')
                            <span class="badge bg-green-lt">Active</span>
                        @elseif(($subscription->status == "inactive"))
                            <span class="badge bg-danger-lt">Inactive</span>
                        @else
                            <span class="badge bg-warning-lt">{{ ucfirst($subscription->status)  }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mb-3">
                @livewire(admin_view_path('subscriptions.livewire.subscription-action-card'), ['subscription' => $subscription])
            </div>

            @livewire(admin_view_path('livewire.log'), ['model' => \App\Models\Subscription::class, 'model_id' => $subscription->id])
        </div>

        <div class="col-8">
            @if($subscription->status == 'cancelled')
                <div class="alert alert-warning alert-dismissible" role="alert">
                    <h3 class="mb-1">Subscription has been cancelled</h3>
                    <p class="text-secondary">
                        This subscription was cancelled on {{ $subscription->cancelled_at ? $subscription->cancelled_at->format('d M, Y') : 'N/A' }} for <code>{{ $subscription->cancel_reason }}</code>
                        @if($subscription->next_billing_at && $subscription->next_billing_at->isFuture())
                            The subscription will remain active until {{ $subscription->next_billing_at->format('d M, Y') }}.
                        @else
                            The subscription is no longer active and will not renew again.
                        @endif
                    </p>
                </div>
            @endif

            <div class="mb-3">
                @livewire(admin_view_path('subscriptions.livewire.subscription-info-card'), ['subscription' => $subscription])
            </div>

            @if($subscription->user)
                <div class="mb-3">
                    @livewire(admin_view_path('users.livewire.user-info-card'), ['user' => $subscription->user])
                </div>
            @endif
        </div>
    </div>
@endsection
