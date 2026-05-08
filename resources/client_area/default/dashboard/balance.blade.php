@extends('theme::dashboard.dashboard-layout')

@section('container')
    <div class="mb-4">
        @livewire(client_view_path('livewire.table'), [
            'title' => 'Balance History',
            'description' => 'View your recent balance transactions.',
            'columns' => [
                'Description',
                'Amount',
                'Balance Before',
                'Date',
            ],
            'rows' =>
                auth()->user()->balanceTransactions()->latest()->get()->map(function($transaction) {
                    return [
                        $transaction->description ? : 'No description provided',
                        "{$transaction->result} " . price($transaction->amount),
                        price($transaction->balance_before_transaction),
                        $transaction->created_at->format('Y-m-d H:i:s'),
                    ];
                })->toArray(),
        ])
    </div>
@endsection
