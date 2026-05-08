<?php

use App\Models\Payment;
use Livewire\Volt\Component;
use App\Models\GatewayConfig;
use Illuminate\Validation\Rule;

new class extends Component {
    public $payment;

    public $gateway_config_id;

    #[\Livewire\Attributes\Locked]
    public array $gateways = [];

    #[\Livewire\Attributes\Locked]
    public array $supportsPartialRefund = [];

    public $amount = 0;

    public $reason = '';

    public function mount($payment)
    {
        $this->payment = $payment;

        $balanceGateway = GatewayConfig::where('extension_identifier', 'gateway-balance')->first();
        if ($balanceGateway) {
            $this->gateways[$balanceGateway->id] = $balanceGateway->display_name;
            $this->gateway_config_id = $balanceGateway->id;

            if ($balanceGateway->gateway->supportsPartialRefunds()) {
                // add the balance gateway to the list of gateways that support partial refunds
                $this->supportsPartialRefund[$balanceGateway->id] = true;
            }
        }

        if ($this->payment->gatewayConfig) {
            // add the gateway of the payment to the list
            $this->gateways[$this->payment->gatewayConfig->id] = $this->payment->gatewayConfig->display_name . ' (Original)';

            if ($this->payment->gatewayConfig->gateway->supportsPartialRefunds()) {
                // add the original gateway to the list of gateways that support partial refunds
                $this->supportsPartialRefund[$this->payment->gatewayConfig->id] = true;
            }
        }

        $this->amount = $this->payment->total();
    }

    public function refund()
    {
        abort_if(!auth()->user()->hasPerm('admin.payments.refund'), 403);

        // validate the input
        $this->validate([
            'gateway_config_id' => ['required', Rule::in(array_keys($this->gateways))],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        Payment::actions()->refundPaymentAsAdmin([
            'payment_id' => $this->payment->id,
            'admin_user_id' => auth()->id(),
            'gateway_config_id' => $this->gateway_config_id,
            'amount' => $this->amount,
            'reason' => $this->reason,
        ]);

        $this->redirect(route('admin.payments.edit', ['payment' => $this->payment->id]), true);
    }
}

?>

<div class="offcanvas offcanvas-end" wire:ignore.self tabindex="-1" id="refundOrderDrawer"
     aria-labelledby="refundOrderDrawerLabel" aria-modal="true" role="dialog">
    <div class="offcanvas-header">
        <h2 class="offcanvas-title" id="refundOrderDrawerLabel">Refund Payment</h2>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="row">
            <div class="form-group col-md-12 col-12 mb-3">
                <x-admin::form.label>Gateway</x-admin::form.label>
                <x-admin::form.select wire:model.change="gateway_config_id" :value="$gateway_config_id"
                                      :options="$gateways" class="mb-2" searchable/>
                @error('gateway_config_id')
                <x-admin::form.error :message="$message"/>
                @else
                    <x-admin::form.description>Select the gateway to which the payment should be refunded to
                    </x-admin::form.description>
                    @enderror
            </div>
            @if(in_array($gateway_config_id, array_keys($supportsPartialRefund)))
                <div class="form-group col-md-12 col-12 mb-3">
                    <x-admin::form.label>Refund Amount ({{ $payment->currency }})</x-admin::form.label>
                    <x-admin::form.input wire:model="amount"></x-admin::form.input>
                    @error('amount')
                    <x-admin::form.error :message="$message"/>
                    @else
                        <x-admin::form.description>Enter the amount you wish to refund</x-admin::form.description>
                        @enderror
                </div>
            @endif
            <div class="form-group col-md-12 col-12">
                <x-admin::form.label>Refund Reason (Optional)</x-admin::form.label>
                <x-admin::form.input wire:model="reason"></x-admin::form.input>
                @error('reason')
                    <x-admin::form.error :message="$message"/>
                @else
                    <x-admin::form.description>The reason for the refund</x-admin::form.description>
                @enderror
            </div>
            @error('payment_id')
            <x-admin::form.error :message="$message"/>
            @endif
        </div>
        <div class="mt-3 text-end">
            <button class="btn btn-primary" wire:click="refund" wire:confirm type="button">
                Issue Refund
            </button>
        </div>
    </div>
</div>

