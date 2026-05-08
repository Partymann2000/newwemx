<?php

use Livewire\Volt\Component;
use App\Models\User;

new class extends Component
{
    public $user;

    public $amount = 0;

    public $type = '+';

    public $description = '';

    public function mount(User $user)
    {
        $this->user = $user;
    }

    #[\Livewire\Attributes\On('user-updated')]
    public function userUpdated()
    {

    }

    public function updateBalance()
    {
        abort_if(!auth()->user()->hasPerm('admin.users.update'), 403);

        $this->resetErrorBag();

        User::actions()->updateUserBalanceAsAdmin([
            'user_id' => $this->user->id,
            'amount' => $this->amount,
            'type' => $this->type,
            'description' => $this->description,
        ]);

        $this->dispatch('user-updated');

        $this->reset([
            'amount',
            'type',
            'description',
        ]);
    }
}

?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Account Balance</h3>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="form-group col-md-8 col-8">
                <x-admin::form.label for="amount" label="Amount" />
                <x-admin::form.input type="number" wire:model="amount" id="amount" name="amount" value="0" step="0.01" required />
                @error('amount')
                    <x-admin::form.error message="{{ $message }}" />
                @enderror
            </div>
            <div class="form-group col-md-4 col-4">
                <x-admin::form.label for="type" label="Type" />
                <x-admin::form.select name="type" wire:model="type" id="type" :options="['+' => 'ADD', '-' => 'REMOVE', '=' => 'SET']" />
                @error('type')
                    <x-admin::form.error message="{{ $message }}" />
                @enderror
            </div>
            <div class="form-group col-md-12 col-12">
                <x-admin::form.label for="description" label="Description" />
                <x-admin::form.input type="text" wire:model="description" id="description" name="description" rows="2" />
                @error('description')
                    <x-admin::form.error message="{{ $message }}" />
                @enderror
            </div>
            <div class="col-12" style="display: flex;justify-content: space-between;">
                <div class="profile-widget-name">
                    Current Balance:
                    <strong>{{ price($user->balance) }}</strong>
                </div>
                <button class="btn btn-primary" type="submit" wire:click="updateBalance">
                    Update Balance
                </button>
            </div>
        </div>
    </div>
    @if($user->balanceTransactions->isNotEmpty())
    <div class="card-table table-responsive">
        <table class="table table-vcenter">
            <thead>
            <tr>
                <th>Description</th>
                <th>Amount</th>
                <th>Date</th>
            </tr>
            </thead>
            <tbody>

            @foreach($user->balanceTransactions()->latest()->get() as $transaction)
            <tr>
                <td class="td-truncate">
                    <div class="text-truncate">{{ $transaction->description ? $transaction->description : 'No description given' }}</div>
                </td>
                <td class="text-nowrap text-secondary" data-bs-toggle="tooltip"
                    data-bs-placement="top" title="Before transaction: {{ price($transaction->balance_before_transaction) }}">
                    @if($transaction->result === '+')
                        <div class="text-success">+ {{ price($transaction->amount) }}</div>
                    @elseif($transaction->result === '-')
                        <div class="text-danger">- {{ price($transaction->amount) }}</div>
                    @else
                        <div>= {{ price($transaction->amount) }}</div>
                    @endif
                </td>
                <td class="text-nowrap text-secondary">{{ $transaction->created_at->format('d M Y') }}</td>
            </tr>
            @endforeach

            </tbody>
        </table>
    </div>
    @endif
</div>
