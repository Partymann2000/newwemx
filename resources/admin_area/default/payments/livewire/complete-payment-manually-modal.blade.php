<?php

use Livewire\Volt\Component;

new class extends Component
{
    public $payment;

    public $transaction_id;

    public $gateway_id;

    public function mount($payment)
    {
        $this->payment = $payment;
    }

    public function completePaymentManually()
    {
        abort_if(!auth()->user()->hasPerm('admin.payments.complete_manually'), 403);

        if($this->payment->isPaid()) {
            return;
        }

        // Complete payment manually
        $this->payment->completeManually($this->gateway_id, $this->transaction_id);
    }
}


?>

<div wire:ignore.self class="modal modal-blur fade" id="completePaymentManuallyModal" tabindex="-1">
    <div class="modal-dialog" role="document">
        <form wire:submit="completePaymentManually()">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Complete Payment Manually</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Transaction ID (Optional)</label>
                        <x-admin::form.input wire:model="transaction_id" type="text" placeholder="Transaction ID (optional)" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gateway</label>
                        <x-admin::form.select wire:model="gateway_id" :options="\App\Models\GatewayConfig::pluck('display_name', 'id')" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Complete</button>
                </div>
            </div>
        </form>
    </div>
</div>
