<?php

use Livewire\Volt\Component;
use App\Models\Payment;

new class extends Component
{
    public $payment;

    public function mount(Payment $payment)
    {
        $this->payment = $payment;
    }
}

?>

<div class="card">
    <div class="card-header">
        <div>
            <div class="row align-items-center">
                <div class="col">
                    <div class="card-title">{{ price($payment->total(), in: $payment->currency, to: $payment->currency) }}</div>
                    <div class="card-subtitle">{{ $payment->description }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="datagrid">
            <div class="datagrid-item">
                <div class="datagrid-title">{{ __('messages.description') }}</div>
                <div class="datagrid-content">{{ $payment->description }}</div>
            </div>
            <div class="datagrid-item">
                <div class="datagrid-title">Payment ID</div>
                <div class="datagrid-content">{{ $payment->id }}</div>
            </div>
            <div class="datagrid-item">
                <div class="datagrid-title">{{ __('messages.invoice_id') }}</div>
                <div class="datagrid-content">{{ $payment->invoice_id }}</div>
            </div>
            <div class="datagrid-item">
                <div class="datagrid-title">{{ __('messages.status') }}</div>
                <div class="datagrid-content">
                    @if($payment->status == 'paid')
                        <span class="badge bg-green-lt">Paid</span>
                    @elseif(($payment->status == "unpaid"))
                        <span class="badge bg-danger-lt">Unpaid</span>
                    @else
                        <span class="badge bg-info-lt">{{ ucfirst($payment->status)  }}</span>
                    @endif
                </div>
            </div>
            <div class="datagrid-item">
                <div class="datagrid-title">{{ __('messages.amount') }}</div>
                <div class="datagrid-content">{{ price($payment->total(), in: $payment->currency, to: $payment->currency) }}</div>
            </div>
            <div class="datagrid-item">
                <div class="datagrid-title">{{ __('messages.currency') }}</div>
                <div class="datagrid-content">
                    {{ $payment->currency }}
                </div>
            </div>
            <div class="datagrid-item">
                <div class="datagrid-title">{{ __('messages.transaction_id') }}</div>
                <div class="datagrid-content">{{ $payment->transaction_id ?? '-' }}</div>
            </div>
            <div class="datagrid-item">
                <div class="datagrid-title">{{ __('messages.gateway') }}</div>
                <div class="datagrid-content">
                    @if($payment->gatewayConfig)
                        {{ $payment->gatewayConfig->display_name }}
                    @else
                        <span class="badge bg-secondary-lt">None</span>
                    @endif
                </div>
            </div>
            <div class="datagrid-item">
                <div class="datagrid-title">{{ __('messages.user') }}</div>
                <div class="datagrid-content">@if($payment->user) <a href="{{ route('admin.users.edit', $payment->user->id) }}" wire:navigate>{{ $payment->user->username }} ({{ $payment->user->email }})</a> @else <span class="badge bg-secondary-lt">Guest</span> @endif</div>
            </div>
            <div class="datagrid-item">
                <div class="datagrid-title">{{ __('messages.paid_at') }}</div>
                <div class="datagrid-content">{{ $payment->paid_at ? $payment->paid_at->format('d M Y') : '-' }}</div>
            </div>
            <div class="datagrid-item">
                <div class="datagrid-title">{{ __('messages.created_at') }}</div>
                <div class="datagrid-content">{{ $payment->created_at->format(settings('date_format', 'd M Y H:i')) }}</div>
            </div>
        </div>
    </div>
</div>



