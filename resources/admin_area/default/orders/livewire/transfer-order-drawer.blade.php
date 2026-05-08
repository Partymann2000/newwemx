<?php

use Livewire\Volt\Component;
use App\Models\User;

new class extends Component
{
    public $order;

    public $userId;

    public $newUserId;

    public $emailBody;

    public $users = [];

    public function mount()
    {
        $this->userId = $this->order->user_id;
        $this->emailBody = 'You now have access to this order. Find the details below.';
        $this->users = User::all()->mapWithKeys(function ($user) {
            return [$user->id => $user->username . ' (' . $user->email . ')'];
        })->toArray();
    }

    public function transferOrder()
    {
        abort_if(!auth()->user()->hasPerm('admin.orders.update'), 403);

        $this->order->actions()->transferOrderAsAdmin([
            'order_id' => $this->order->id,
            'user_id' => $this->newUserId,
            'email_body' => $this->emailBody,
        ]);

        $this->dispatch('order-updated');
    }
}

?>

<div class="offcanvas offcanvas-end" tabindex="-1" id="transferOrderDrawer" aria-labelledby="transferOrderDrawerLabel" aria-modal="true" role="dialog">
    <div class="offcanvas-header">
        <h2 class="offcanvas-title" id="transferOrderDrawerLabel">Transfer Order</h2>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="row">
            <div class="form-group col-md-12 col-12 mb-3">
                <x-admin::form.label>User ID</x-admin::form.label>
                <x-admin::form.select type="date" wire:model="newUserId" :value="$order->user_id" :options="$users" searchable />
                <x-admin::form.description>Select the user you wish to transfer the order to</x-admin::form.description>
            </div>
            <div class="form-group col-md-12 col-12">
                <x-admin::form.label>Email (Optional)</x-admin::form.label>
                <x-admin::form.textarea wire:model="emailBody"></x-admin::form.textarea>
                <x-admin::form.description>Leave this field empty to send no notification</x-admin::form.description>
            </div>
        </div>
        <div class="mt-3 text-end">
            <button class="btn btn-primary" wire:click="transferOrder" type="button" data-bs-dismiss="offcanvas">Update</button>
        </div>
    </div>
</div>

