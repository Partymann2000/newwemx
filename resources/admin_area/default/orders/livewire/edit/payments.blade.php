<?php

use Livewire\Volt\Component;
new class extends Component
{
    public $order;
    public $payments;

    public function mount($order)
    {
        $this->order = $order;
        $this->payments = $this->order->payments()
            ->with(['gatewayConfig'])
            ->latest()
            ->get();
    }
}

?>

<div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Invoice</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Gateway</th>
                    <th>Amount</th>
                    <th>Paid At</th>
                    <th>Created</th>
                    <th class="w-1"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->id }}</td>
                        <td>{{ $payment->invoice_id ?? '-' }}</td>
                        <td>{{ $payment->description }}</td>
                        <td>
                            @if($payment->status === 'paid')
                                <span class="badge bg-green-lt">Paid</span>
                            @elseif($payment->status === 'unpaid')
                                <span class="badge bg-red-lt">Unpaid</span>
                            @else
                                <span class="badge bg-secondary-lt">{{ ucfirst($payment->status) }}</span>
                            @endif
                        </td>
                        <td>{{ $payment->gatewayConfig?->display_name ?? '-' }}</td>
                        <td>{{ priceIn($payment->total(), $payment->currency) }}</td>
                        <td>{{ $payment->paid_at?->translatedFormat('d M Y H:i') ?? '-' }}</td>
                        <td>{{ $payment->created_at?->translatedFormat('d M Y H:i') ?? '-' }}</td>
                        <td>
                            <a href="{{ route('admin.payments.edit', $payment->id) }}" wire:navigate>Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-secondary text-center py-4">
                            No payments are linked to this order yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
