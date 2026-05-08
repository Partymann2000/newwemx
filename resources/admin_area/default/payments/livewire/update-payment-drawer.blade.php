<?php

use App\Models\Currency;
use App\Models\Payment;
use Livewire\Volt\Component;
use App\Models\User;

new class extends Component {
    public $payment;

    public $user_id;

    public $description;

    public $invoice_id;

    public $transaction_id;

    public $subtotal;

    public $currency;

    public $status;

    public $userOptions = [];

    public $statusOptions = [
        'paid' => 'Paid',
        'unpaid' => 'Unpaid',
        'refunded' => 'Refunded',
    ];

    public $currencyOptions = [];

    public function mount($payment)
    {
        $this->payment = $payment;
        $this->user_id = $payment->user_id;
        $this->description = $payment->description;
        $this->transaction_id = $payment->transaction_id;
        $this->invoice_id = $payment->invoice_id;
        $this->subtotal = $payment->subtotal;
        $this->currency = $payment->currency;
        $this->status = $payment->status;

        $this->userOptions = User::get()->mapWithKeys(function ($user) {
            return [$user->id => $user->username . ' (' . $user->email . ')'];
        })->toArray();

        $this->currencyOptions = Currency::pluck('display_name', 'currency')->toArray();
    }

    public function updatePayment()
    {
        abort_if(!auth()->user()->hasPerm('admin.payments.update'), 403);

        Payment::actions()->updatePaymentAsAdmin([
            'payment_id' => $this->payment->id,
            'user_id' => $this->user_id,
            'description' => $this->description,
            'invoice_id' => $this->invoice_id,
            'transaction_id' => $this->transaction_id,
            'subtotal' => $this->subtotal,
            'currency' => $this->currency,
            'status' => $this->status,
        ]);

        $this->dispatch('payment-updated');
        $this->redirect(route('admin.payments.edit', ['payment' => $this->payment->id]), true);
    }
}

?>

<div class="offcanvas offcanvas-end" wire:ignore.self tabindex="-1" id="updatePaymentDrawer"
     aria-labelledby="updatePaymentDrawerLabel" aria-modal="true" role="dialog">
    <div class="offcanvas-header">
        <h2 class="offcanvas-title" id="updatePaymentDrawerLabel">Update Payment</h2>
        <button type="button" class="btn-close text-reset" id="closeUpdatePaymentDrawer" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="row">

            <div class="form-group col-md-12 col-12 mb-3">
                <x-admin::form.label>User</x-admin::form.label>
                <x-admin::form.select wire:model="user_id" :value="$user_id" :options="$userOptions" class="mb-2"
                                      searchable/>
                @error('user_id')
                <x-admin::form.error :message="$message"/>
                @enderror
            </div>

            <div class="form-group col-md-12 col-12 mb-3">
                <x-admin::form.label>Description</x-admin::form.label>
                <x-admin::form.input wire:model.change="description" class="mb-2"/>
                @error('description')
                <x-admin::form.error :message="$message"/>
                @enderror
            </div>

            <div class="form-group col-md-12 col-12 mb-3">
                <x-admin::form.label>Amount</x-admin::form.label>
                <x-admin::form.input wire:model="subtotal" class="mb-2" type="number" step="0.01"/>
                @error('subtotal')
                    <x-admin::form.error :message="$message"/>
                @enderror
            </div>

            <div class="form-group col-md-12 col-12 mb-3">
                <x-admin::form.label>Currency</x-admin::form.label>
                <x-admin::form.select wire:model="currency" :value="$currency" :options="$currencyOptions" class="mb-2"
                                      searchable/>
                @error('currency')
                    <x-admin::form.error :message="$message"/>
                @enderror
            </div>

            <div class="form-group col-md-12 col-12 mb-3">
                <x-admin::form.label>Status</x-admin::form.label>
                <x-admin::form.select wire:model="status" :value="$status" :options="$statusOptions" class="mb-2"
                                      searchable/>
                @error('status')
                    <x-admin::form.error :message="$message"/>
                @enderror
            </div>

            <div class="form-group col-md-12 col-12 mb-3">
                <x-admin::form.label>Invoice ID</x-admin::form.label>
                <x-admin::form.input wire:model="invoice_id" class="mb-2"/>
                @error('invoice_id')
                <x-admin::form.error :message="$message"/>
                @enderror
            </div>

            <div class="form-group col-md-12 col-12 mb-3">
                <x-admin::form.label>Transaction ID</x-admin::form.label>
                <x-admin::form.input wire:model="transaction_id" class="mb-2"/>
                @error('transaction_id')
                <x-admin::form.error :message="$message"/>
                @enderror
            </div>
        </div>
        <div class="mt-3 text-end">
            <button class="btn btn-primary" wire:loading.attr="disabled" wire:click="updatePayment" type="button">
                Update
            </button>
        </div>
    </div>
</div>

