@extends('admin::layouts.wrapper', [
    'activePage' => 'categories',
])

@section('title', __('messages.create_category'))

@section('content')
    <div class="col-12">
        @livewire(admin_view_path('categories.livewire.create-category-form'))
    </div>
@endsection
