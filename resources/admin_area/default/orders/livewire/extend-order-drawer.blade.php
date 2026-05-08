<?php

use Livewire\Volt\Component;

new class extends Component
{
    public $order;

    public $newDueDate;

    public $emailBody;

    public function mount()
    {
        $this->newDueDate = $this->order->due_date?->format('Y-m-d') ?? now()->toDateString();
        $this->emailBody = 'Our team has updated the due date of your order. Find the details below.';
    }

    public function extendOrder()
    {
        abort_if(!auth()->user()->hasPerm('admin.orders.update'), 403);

        $this->order->actions()->extendOrderAsAdmin([
            'order_id' => $this->order->id,
            'due_date' => $this->newDueDate,
            'email_body' => $this->emailBody,
        ]);

        $this->dispatch('order-updated');
    }
}

?>

<div class="offcanvas offcanvas-end" tabindex="-1" id="extendOrderDrawer" aria-labelledby="extendOrderDrawerLabel" aria-modal="true" role="dialog">
    <div class="offcanvas-header">
        <h2 class="offcanvas-title" id="extendOrderDrawerLabel">Extend Order</h2>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="row">
            <div class="form-group col-md-12 col-12 mb-3">
                <x-admin::form.label>New Due Date</x-admin::form.label>
                <x-admin::form.input type="date" wire:model="newDueDate" />
                <x-admin::form.description>The current due date is {{ $order->due_date?->format('d M Y') ?? 'Never' }}</x-admin::form.description>
            </div>
            <div class="form-group col-md-12 col-12">
                <x-admin::form.label>Email (Optional)</x-admin::form.label>
                <x-admin::form.textarea wire:model="emailBody"></x-admin::form.textarea>
                <x-admin::form.description>Leave this field empty to send no notification</x-admin::form.description>
            </div>
        </div>
        <div class="mt-3 text-end">
            <button class="btn btn-primary" wire:click="extendOrder" type="button" data-bs-dismiss="offcanvas">Update</button>
        </div>
    </div>
</div>

