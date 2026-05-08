<?php

use App\Models\Payment;
use App\Models\Subscription;
use Livewire\Volt\Component;

new class extends Component {

    public $subscriptionOptions = [];

    public $subscription_id;

    public $reason;

    public function mount()
    {
        // format options as id => description (id)
        $this->subscriptionOptions = auth()->user()->subscriptions()->where('status', 'active')->get()->mapWithKeys(function($subscription) {
            return [$subscription->id => $subscription->description . ' (' . $subscription->subscription_id . ')'];
        })->toArray();

        $this->subscription_id = array_key_first($this->subscriptionOptions) ?? 0;
    }

    public function cancelSubscription()
    {
        $this->resetErrorBag();

        Subscription::actions()->cancelSubscriptionAsClient([
            'user_id' => auth()->id(),
            'subscription_id' => $this->subscription_id,
            'reason' => $this->reason,
        ]);

        $this->redirect(route('subscriptions.index'), true);
    }
}

?>

    <!-- Cancel Subscription Drawer -->
<x-theme::drawer id="cancel-subscription-drawer" wire:ignore.self>
    <x-theme::drawer.title text="Cancel Subscription"/>
    <x-theme::drawer.close-button drawer_id="cancel-subscription-drawer"/>
    <x-theme::text.p class="text-sm mb-6" text="Select the subscription you wish to cancel"/>

    <div class="mb-4">
        <div class="mb-3">
            <x-theme::form.label for="subscription_id" text="Select Subscription"/>
            <x-theme::form.select wire:model="subscription_id" id="subscription_id"
                                  :options="$subscriptionOptions"
                                  placeholder="Select Subscription"/>
            @error('subscription_id')
            <x-theme::form.error :text="$message"/>
            @else
                <x-theme::form.description text="Select the subscription you wish to cancel."/>
                @enderror
        </div>
    </div>

    <div class="mb-4">
        <div class="mb-3">
            <x-theme::form.label for="reason" text="Cancellation Reason"/>
            <x-theme::form.textarea wire:model="reason" id="reason" placeholder="Cancellation Reason"/>
            @error('reason')
            <x-theme::form.error :text="$message"/>
            @else
                <x-theme::form.description
                    text="Briefly describe the reason for cancellation"/>
                @enderror
        </div>
    </div>

    <x-theme::button.primary wire:click="cancelSubscription()" wire:confirm="" class="w-full"
                             text="Cancel Subscription"/>
</x-theme::drawer>
