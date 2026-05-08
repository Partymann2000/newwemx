@extends('theme::dashboard.dashboard-layout', [
    'activePage' => 'dashboard',
])

@section('title', 'Email Inbox')

@section('container')
    <div class="mb-4">
        @livewire(client_view_path('livewire.table'), [
            'title' => 'Email Inbox',
            'description' => 'View emails sent to your account.',
            'columns' => [
                'Subject',
                'From',
                'Status',
                'Seen at',
                'Date',
                'Actions',
            ],
            'rows' =>
                auth()->user()->emails()->where('display', 1)->latest()->get()->map(function($email) {
                    return [
                        $email->subject,
                        $email->from,
                        ucfirst($email->status),
                        $email->seen_at ? $email->seen_at->format(settings('date_format', 'd M Y H:i')) : 'Unseen',
                        $email->created_at->format(settings('date_format', 'd M Y H:i')),
                        '<a href="'. route('emails.view', $email->id) .'" target="_blank" class="text-blue-600 dark:text-blue-500 hover:underline">View</a>',
                    ];
                })->toArray(),
        ])
    </div>
@endsection
