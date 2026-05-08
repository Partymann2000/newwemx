@extends('theme::orders.layout', [
    'activeTab' => 'emails',
])

@section('container')
    <div class="mb-4">
        @livewire(client_view_path('livewire.table'), [
            'title' => 'Emails',
            'description' => 'View all emails sent related to this order.',
            'columns' => [
                'Subject',
                'From',
                'To',
                'Status',
                'Sent At',
                '',
            ],
            'rows' =>
                $order->emails->map(function($email) {
                    return [
                        $email->subject,
                        $email->from,
                        $email->to,
                        ucfirst($email->status),
                        $email->created_at->format(settings('date_format', 'd M Y H:i')),
                        $email->user_id == auth()->id() ? '<a target="_blank" href="'. route('emails.view', $email->id) .'" class="text-blue-600 hover:underline">View</a>' : '',
                    ];
                })->toArray(),
        ])
    </div>
@endsection
