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

    public function cancelSubscription()
    {
        abort_if(!auth()->user()->hasPerm('admin.subscriptions.perform_actions'), 403);

        $this->subscription->actions()->cancelSubscriptionAsAdmin([
            'subscription_id' => $this->subscription->id,
            'admin_id' => auth()->id(),
        ]);

        // dispatch event
        $this->redirect(route('admin.subscriptions.edit', $this->subscription->id), true);
    }

    public function checkSubscription()
    {
        abort_if(!auth()->user()->hasPerm('admin.subscriptions.perform_actions'), 403);

        $this->subscription->check();

        // dispatch event
        $this->dispatch('subscription-updated', ['subscription_id' => $this->subscription->id]);
    }
}

?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Actions</h3>
    </div>
    <div class="card-body">
        @if($subscription->status == 'active')
        <button type="button" class="btn btn-danger" wire:click="cancelSubscription" wire:confirm="Are you sure you want to cancel this subscription?" onclick="isLoading(this)">
            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-cancel"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M18.364 5.636l-12.728 12.728" /></svg>
            Cancel Subscription
        </button>
        <button type="button" class="btn btn-primary" wire:click="checkSubscription" onclick="isLoading(this)">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-refresh"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>
            Check Subscription
        </button>
        @endif
        @if($subscription->manage_url)
            <a href="{{ $subscription->manage_url }}" target="_blank" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-external-link"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 13v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h6" /><path d="M15 3h6v6" /><path d="M10 14l11 -11" /></svg>
                Manage Subscription
            </a>
        @endif
    </div>
</div>



