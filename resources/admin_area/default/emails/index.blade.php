@extends('admin::layouts.wrapper', [
    'activePage' => 'emails',
])

@section('title', __('messages.email_history'))

@section('content')
    {{--  Email History Table  --}}
    @livewire(admin_view_path('livewire.table'), [
        'title' => __('messages.email_history'),
        'entries' => 15,
        'columns' => [
            __('messages.id'),
            __('messages.user'),
            __('messages.subject'),
            __('messages.to'),
            __('messages.status'),
            __('messages.created_at'),
            '',
        ],
        'sortableColumns' => [
            __('messages.id'),
            __('messages.user'),
            __('messages.subject'),
            __('messages.to'),
            __('messages.status'),
            __('messages.created_at'),
        ],
        'rows' =>\App\Models\Email::where('display', 1)->latest()->get()->map(function ($extension) {
            return [
                $extension->id,
                $extension->user ? '<div class="d-flex py-1 align-items-center"><span class="avatar avatar-2 me-2" style="background-image: url(' . $extension->user->getAvatarUrl() . ')"></span><div class="flex-fill"><div class="font-weight-medium"><a href="' . route('admin.users.edit', $extension->user_id) . '" wire:navigate class="text-reset">' . $extension->user->full_name . ' (' . $extension->user->username . ')</a></div><div class="text-secondary"><a href="'. route('admin.users.edit', $extension->user_id) .'" wire:navigate class="text-reset">' . $extension->user->email . '</a></div></div></div>' : '<span class="badge bg-secondary-lt">Guest</span>',
                Str::limit($extension->subject, 50),
                $extension->to,
                $extension->status == 'delivered' ? '<span class="badge bg-green-lt">Delivered</span>' : ($extension->status == 'read' ? '<span class="badge bg-info-lt">Read</span>' : ($extension->status == 'failed' ? '<span class="badge bg-danger-lt">Failed</span>' : '<span class="badge bg-warning-lt">' . ucfirst($extension->status) . '</span>')),
                $extension->created_at->translatedFormat('d M Y H:i'),
                '<a href="' . route('admin.emails.view', $extension->id) . '" target="_blank">' . __('messages.view') . '</a>'
            ];
        })->toArray(),
    ])
@endsection
