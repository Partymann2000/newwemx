@extends('admin::layouts.wrapper', [
    'activePage' => 'schedule-logs',
])

@section('title', 'Schedule Logs')

@section('content')
    {{--  Schedule Logs Table  --}}
    @livewire(admin_view_path('livewire.table'), [
        'title' => 'Schedule Logs',
        'entries' => 15,
        'columns' => [
            'event',
            'Description',
            'Status',
            __('messages.created_at'),
            '',
        ],
        'rows' =>\App\Models\AppTaskLog::where('show', true)->latest()->get()->map(function ($log) {
            return [
                $log->task,
                $log->message,
                ucfirst($log->status),
                $log->created_at->format('Y-m-d H:i:s'). ' ('. $log->created_at->diffForHumans() .')',
                '<a href="' . route('admin.schedule-logs.view', $log->id) . '" wire:navigate>View</a>',
            ];
        })->toArray(),
    ])
@endsection
