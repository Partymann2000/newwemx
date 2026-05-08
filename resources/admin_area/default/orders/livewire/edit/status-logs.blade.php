<?php

use Livewire\Volt\Component;

new class extends Component
{
    public $order;

    public function mount($order)
    {
        $this->order = $order;

    }
}

?>

<div>
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

        @foreach($order->logs as $log)
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
