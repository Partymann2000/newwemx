@extends('theme::dashboard.dashboard-layout')

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
                'Date',
                'Actions',
            ],
            'rows' =>
                auth()->user()->payments->where('status', 'paid')->map(function($payment) {
                    return [
                        $payment->description,
                        priceIn($payment->total(), $payment->currency),
                        $payment->currency,
                        ucfirst($payment->status),
                        $payment->gatewayConfig ? $payment->gatewayConfig->display_name : 'None',
                        $payment->created_at->format(settings('date_format', 'd M Y H:i')),
                        '<a href="'. route('payments.view', $payment->token) .'" wire:navigate class="font-medium text-blue-600 dark:text-blue-500 hover:underline">View</a>',
                    ];
                })->toArray(),
        ])
    </div>
@endsection
