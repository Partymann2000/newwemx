@extends('admin::layouts.wrapper', [
    'activePage' => 'updates',
])

@section('title', 'Updates')

@section('actions')
    <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">
            <a href="{{ \App\Services\WemxGitHubReleases::RELEASES_PAGE_URL }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary">
                View on GitHub
            </a>
        </div>
    </div>
@endsection

@section('content')
    @livewire(admin_view_path('updates.livewire.releases-timeline'))
@endsection
