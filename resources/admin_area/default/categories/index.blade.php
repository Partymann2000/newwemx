@extends('admin::layouts.wrapper', [
    'activePage' => 'categories',
])

@section('title', __('messages.categories'))

@section('actions')
    <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">
            <x-admin::button href="{{ route('admin.categories.create') }}"
                             wire:navigate>{{ __('messages.create') }}</x-admin::button>
        </div>
    </div>
@endsection

@section('content')
    {{--  Categories Table  --}}
    @livewire(admin_view_path('livewire.table'), [
        'title' => __('messages.categories'),
        'entries' => 15,
        'columns' => [
            __('messages.id'),
            __('messages.name'),
            __('messages.slug'),
            'Status',
            __('messages.created_at'),
            '',
        ],
        'sortableColumns' => [
            __('messages.id'),
            __('messages.name'),
            __('messages.slug'),
            __('messages.created_at'),
        ],
        'rows' =>\App\Models\Category::get()->map(function ($category) {
            return [
                $category->id,
                '<div class="d-flex align-items-center"><img src="' . $category->icon() . '" style="margin-right: 10px; height: 40px; width: 40px"/><div class="d-flex flex-column"><a href="' . route('admin.categories.edit', $category->id) . '" wire:navigate>' . $category->name . '</a>' . Str::limit($category->description, 40) . '</div></div>',
                $category->slug,
                '<span class="badge bg-' . match ($category->status) {
                    'active' => 'success',
                    'restricted' => 'warning',
                    'unlisted' => 'info',
                    'inactive' => 'secondary',
                    default => 'danger',
                } . '-lt">' . ucfirst($category->status) . '</span>',
                $category->created_at->translatedFormat('d M Y'),
                '<a href="' . route('admin.categories.edit', $category->id) . '" wire:navigate>' . __('messages.edit') . '</a>'
            ];
        })->toArray(),
    ])
@endsection
