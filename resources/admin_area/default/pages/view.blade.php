@extends('admin::layouts.wrapper', [
    'activePage' => 'pages',
])

@section('title', __('messages.view_page'))

@section('actions')
    <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">
            <x-admin::button href="{{ route('admin.pages.edit', $page->id) }}"
                             wire:navigate>{{ __('messages.edit') }}</x-admin::button>
        </div>
    </div>
@endsection

@section('content')
    <div class="col-12">
        @livewire(admin_view_path('pages.livewire.view-page-card'), ['page' => $page])
    </div>
@endsection
