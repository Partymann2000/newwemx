<?php

use Livewire\Volt\Component;

new class extends Component
{
    public $order;
    public $logCount;

    public function mount($order)
    {
        $this->order = $order;
        $this->logCount = $order->logs()->count();
    }

    #[\Livewire\Attributes\On('order-updated')]
    public function orderUpdated()
    {
        $this->logCount = $this->order->logs()->count();
    }
} 
?>

@php
    $latestThreeLogs = $order
        ->logs()
        ->orderByDesc('created_at')
        ->take(3)
        ->get()
        ->reverse();
@endphp


<div class="card">
    <div class="card-body">
        <h3 class="card-title">Order History</h3>
        <ul class="steps steps-vertical">

            @if($orderCreatorAdmin = $order->logs()->where('action', 'order_created_as_admin')->first())
            <li class="step-item">
                <div class="h4 m-0">
                    Order Created by Admin
                </div>
                <div class="text-secondary">
                    The order was created by an administrator {{ $orderCreatorAdmin->user ? $orderCreatorAdmin->user->username : 'system' }} through the admin panel.
                </div>
                <div class="text-secondary">
                    {{ $order->created_at->diffForHumans() }} ({{ $order->created_at->format('d M Y') }}) by {{ $order->user->username }}
                </div>
            </li>
            @else
            <li class="step-item">
                <div class="h4 m-0">Order Created</div>
                <div class="text-secondary">
                    The order was purchased by the user.
                </div>
                <div class="text-secondary">
                    {{ $order->created_at->diffForHumans() }} ({{ $order->created_at->format('d M Y') }}) by {{ $order->user->username }}
                </div>
            </li>
            @endif

            @if($logCount > 3)
                <li class="step-item">
                    <div class="h4 m-0">{{ $logCount - 3 }} more events</div>
                    <div class="text-secondary">
                        There are {{ $logCount - 3 }} more events in the history.
                    </div>
                    <div class="text-secondary">
                        <a href="{{ route('admin.orders.edit', ['order' => $order->id, 'orderEditPage' => 'logs']) }}" wire:navigate>View All</a>
                    </div>
                </li>
            @endif

            @foreach($latestThreeLogs as $log)
                @if($log->action == 'order_created_as_admin')
                    @continue
                @endif
                <li class="step-item @if($loop->last) active @endif">
                    <div class="h4 m-0">
                        @if($log->action == 'order_suspended')
                            Order Suspended
                        @elseif($log->action == 'order_unsuspended')
                            Order Unsuspended
                        @elseif($log->action == 'order_terminated')
                            Order Terminated
                        @else
                            {{ $log->action }}
                        @endif
                    </div>
                    <div class="text-secondary">
                        {{ $log->description }}
                    </div>
                    <div class="text-secondary">
                        {{ $log->created_at->diffForHumans() }}
                        ({{ $log->created_at->format('d M Y') }})
                        @if($log->user)
                            by {{ $log->user->username }}
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>
