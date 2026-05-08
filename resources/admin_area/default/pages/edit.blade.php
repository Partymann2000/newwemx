@extends('admin::layouts.wrapper', [
    'activePage' => 'pages',
])

@section('title', __('messages.edit_page'))

@section('content')
    <div class="col-12">
        @livewire(admin_view_path('pages.livewire.edit-page-form'), ['page' => $page])
    </div>
@endsection
