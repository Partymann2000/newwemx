@extends('admin::layouts.wrapper', [
    'activePage' => 'categories',
])

@section('title', __('messages.edit_category'))

@section('content')
    <div class="col-12">
        @livewire(admin_view_path('categories.livewire.edit-category-form'), ['category' => $category])
    </div>
@endsection
