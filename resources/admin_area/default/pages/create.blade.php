@extends('admin::layouts.wrapper', [
    'activePage' => 'pages',
])

@section('title', __('messages.create_page'))

@section('content')
    <div class="col-12">
        @livewire(admin_view_path('pages.livewire.create-page-form'))
    </div>
@endsection
