<?php

use App\Models\Payment;
use Livewire\Volt\Component;

new class extends Component {

    public $amount;

    public function createBalancePayment()
    {
        $payment = Payment::actions()->createBalancePaymentForClient([
            'user_id' => auth()->id(),
            'amount' => $this->amount,
        ]);

        $this->redirect(route('payments.view', ['payment' => $payment->token]), true);
    }
}

?>

    <!-- Add Balance Drawer -->
<x-theme::drawer id="add-balance-drawer" wire:ignore.self>
    <x-theme::drawer.title text="Top up Account Balance"/>
    <x-theme::drawer.close-button drawer_id="add-balance-drawer"/>
    <x-theme::text.p class="text-sm mb-6" text="Enter the amount you wish to add to your account balance."/>

    <div class="mb-4">
        <div class="mb-3">
            <x-theme::form.label for="amount" text="Amount in {{ baseCurrency() }}"/>
            <x-theme::form.input type="number" wire:model="amount" id="amount" placeholder="Amount"/>
            @error('amount')
                <x-theme::form.error :text="$message"/>
            @else
                <x-theme::form.description
                    text="Minimum amount is {{ price(settings('min_balance_topup_amount', 5), in: baseCurrency(), to: baseCurrency()) }}."/>
            @enderror
        </div>
    </div>

    <x-theme::button.primary wire:click="createBalancePayment" class="w-full" text="Add Balance"/>
</x-theme::drawer>
