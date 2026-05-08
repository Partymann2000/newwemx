@extends('theme::orders.layout', [
    'activeTab' => 'payments',
])

@section('container')
    <div class="mb-4">
        @livewire(client_view_path('livewire.table'), [
            'title' => 'Payments',
            'description' => 'View your recent successful payments.',
            'columns' => [
                'Description',
                'Amount',
                'Currency',
                'Status',
                'Gateway',
                'Transaction ID',
                'Date',
            ],
            'rows' =>
                $order->payments->where('status', 'paid')->map(function($payment) {
                    return [
                        $payment->description,
                        price($payment->total(), $payment->currency),
                        $payment->currency,
                        ucfirst($payment->status),
                        $payment->gatewayConfig ? $payment->gatewayConfig->display_name : 'None',
                        $payment->transaction_id ?? '-',
                        $payment->created_at->format(settings('date_format', 'd M Y H:i')),
                    ];
                })->toArray(),
        ])
    </div>
@endsection
