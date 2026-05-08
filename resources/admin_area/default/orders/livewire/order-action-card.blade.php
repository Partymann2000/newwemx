<?php

use Livewire\Volt\Component;

new class extends Component
{
    public $order;

    public function mount($order)
    {
        $this->order = $order;
    }

    public function suspend()
    {
        abort_if(!auth()->user()->hasPerm('admin.orders.perform_actions'), 403);

        $this->order->actions()->suspendOrderAsAdmin([
            'order_id' => $this->order->id,
        ]);

        // dispatch event
        $this->dispatch('order-updated');
    }

    public function unsuspend()
    {
        abort_if(!auth()->user()->hasPerm('admin.orders.perform_actions'), 403);

        $this->order->actions()->unsuspendOrderAsAdmin([
            'order_id' => $this->order->id,
        ]);

        // dispatch event
        $this->dispatch('order-updated');
    }

    public function terminate()
    {
        abort_if(!auth()->user()->hasPerm('admin.orders.perform_actions'), 403);

        $this->order->actions()->terminateOrderAsAdmin([
            'order_id' => $this->order->id,
        ]);

        // dispatch event
        $this->dispatch('order-updated');
    }

    public function activate()
    {
        abort_if(!auth()->user()->hasPerm('admin.orders.perform_actions'), 403);

        $this->order->actions()->activatePendingOrderAsAdmin([
            'order_id' => $this->order->id,
        ]);

        // dispatch event
        $this->dispatch('order-updated');
    }
}
?>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">Actions</h3>
    </div>
    <div class="card-body">
        @if($order->status === 'pending')
        <button type="button" class="btn btn-primary" wire:click="activate" wire:confirm="Are you sure you want to activate this pending order?" onclick="isLoading(this)">
            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-player-play"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 4v16l13 -8z" /></svg>
            Activate
        </button>
        @endif
        <button type="button" class="btn btn-success" wire:click="unsuspend" wire:confirm="Are you sure you want to unsuspend this order?" onclick="isLoading(this)">
            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-refresh"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>
            Unsuspend
        </button>
        <button type="button" class="btn btn-warning" wire:click="suspend" wire:confirm="Are you sure you want to suspend this order?" onclick="isLoading(this)">
            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-cancel"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M18.364 5.636l-12.728 12.728" /></svg>
            Suspend
        </button>
        <button type="button" class="btn btn-danger" wire:click="terminate" wire:confirm.prompt="Are you sure you want to terminate this order? This action is irreversible. Enter the Order ID to confirm |{{ $order->id }}" onclick="isLoading(this)">
            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-trash"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
            Terminate
        </button>
    </div>
</div>
