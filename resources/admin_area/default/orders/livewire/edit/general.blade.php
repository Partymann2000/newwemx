<?php

use Livewire\Volt\Component;
use App\Models\User;

new class extends Component
{
    public $order;

    public $user_id;

    public $period_in_days;

    public $userOptions = [];

    public $priceCycles = [
        0 => 'One Time',
        1 => '1 Day (Daily)',
        7 => '7 Days (Weekly)',
        14 => '14 Days (Bi-Weekly)',
        30 => '30 Days (Monthly)',
        90 => '90 Days (Quarterly)',
        180 => '180 Days (Semi-Annually)',
        365 => '365 Days (Annually)',
    ];

    public function mount($order)
    {
        $this->order = $order;

        foreach(User::all() as $user) {
            $this->userOptions[$user->id] = $user->username . ' (' . $user->email . ')';
        }
    }

    public function updateOrder()
    {

    }
}

?>

<div>
    <div class="row">
        <div class="col-12 mb-3">
            <x-admin::form.label for="external_id">External ID</x-admin::form.label>
            <x-admin::form.input id="external_id" wire:model="external_id" placeholder="External ID"/>
            @error('external_id')
                <x-admin::form.error :message="$message" />
            @else
                <x-admin::form.description description="The external identifier of the order on the platform it was created" />
            @enderror
        </div>
        <div class="col-12 mb-3">
            <x-admin::form.label for="user_id">{{ __('messages.user') }}</x-admin::form.label>
            <x-admin::form.select id="user_id" wire:model="user_id" :options="$userOptions" searchable :value="$order->user_id"/>
            @error('user_id')
                <x-admin::form.error :message="$message" />
            @enderror
        </div>
        <div class="col-12 mb-3">
            <x-admin::form.label for="period_in_days">{{ __('messages.cycle') }}</x-admin::form.label>
            <x-admin::form.select id="period_in_days" wire:model="period_in_days" :options="$priceCycles" searchable :value="$order->period_in_days"/>
            @error('period_in_days')
                <x-admin::form.error :message="$message" />
            @enderror
        </div>
    </div>
    <div class="text-end">
        <button type="button" wire:click="updateOrder()" class="btn btn-primary">{{ __('messages.update') }}</button>
    </div>
</div>
