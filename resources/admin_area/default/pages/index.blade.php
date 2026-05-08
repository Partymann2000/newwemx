@extends('admin::layouts.wrapper', [
    'activePage' => 'pages',
])

@section('title', __('messages.pages'))

@section('actions')
    <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">
            <x-admin::button href="{{ route('admin.pages.create') }}"
                             wire:navigate>{{ __('messages.create') }}</x-admin::button>
        </div>
    </div>
@endsection

@section('content')
    @livewire(admin_view_path('livewire.table'), [
        'title' => __('messages.pages'),
        'entries' => 15,
        'columns' => [
            __('messages.id'),
            __('messages.title'),
            __('messages.slug'),
            __('messages.status'),
            __('messages.updated_at'),
            '',
        ],
        'sortableColumns' => [
            __('messages.id'),
            __('messages.title'),
            __('messages.slug'),
            __('messages.status'),
            __('messages.updated_at'),
        ],
        'rows' => \App\Models\CustomPage::latest()->get()->map(function ($page) {
            return [
                $page->id,
                '<a href="' . route('admin.pages.view', $page->id) . '" wire:navigate>' . e($page->title) . '</a>',
                $page->slug,
                $page->isActive()
                    ? '<span class="badge bg-success-lt">Active</span>'
                    : '<span class="badge bg-secondary-lt">Inactive</span>',
                $page->updated_at->translatedFormat('d M Y'),
                '<div class="d-flex gap-2"><a href="' . route('pages.view', $page->slug) . '" target="_blank">' . __('messages.view') . '</a><a href="' . route('admin.pages.edit', $page->id) . '" wire:navigate>' . __('messages.edit') . '</a></div>',
            ];
        })->toArray(),
    ])
@endsection
