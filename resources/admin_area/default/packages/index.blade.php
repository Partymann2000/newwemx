@extends('admin::layouts.wrapper', [
    'activePage' => 'packages',
])

@section('title', __('messages.packages'))

@section('actions')
    <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">
            <x-admin::button href="{{ route('admin.packages.create') }}" wire:navigate>{{ __('messages.create') }}</x-admin::button>
        </div>
    </div>
@endsection

@section('content')
    @php
        $packagesWithoutCategory = $packagesWithoutCategory ?? collect();
    @endphp

    @if ($categories->isEmpty() && $packagesWithoutCategory->isEmpty())
        <div class="empty">
            <div class="empty-icon">
                <i class="ti ti-package fs-1 text-secondary"></i>
            </div>
            <p class="empty-title">{{ __('messages.packages') }}</p>
            <p class="empty-subtitle text-secondary">
                No packages found. Create a package or assign packages to a category.
            </p>
            <div class="empty-action">
                <x-admin::button href="{{ route('admin.packages.create') }}" wire:navigate>{{ __('messages.create') }}</x-admin::button>
            </div>
        </div>
    @else
        @foreach ($categories as $category)
            <div class="card mb-3 shadow-sm">
                <div class="card-header border-0 py-3 px-3 bg-transparent">
                    <div class="row align-items-center flex-fill">
                        <div class="col-auto">
                            <span class="avatar avatar-md rounded" style="background-image: url({{ $category->icon() }}); background-size: contain;"></span>
                        </div>
                        <div class="col min-w-0">
                            <h2 class="card-title mb-0 text-truncate">
                                <a href="{{ route('admin.categories.edit', $category) }}" wire:navigate class="text-reset">{{ $category->name }}</a>
                            </h2>
                            <div class="text-secondary small">
                                {{ $category->packages->count() }} {{ Str::plural('package', $category->packages->count()) }} ·
                                <span class="badge bg-secondary-lt">{{ $category->slug }}</span>
                            </div>
                        </div>
                        <div class="col-auto ms-auto d-none d-md-block">
                            <a href="{{ route('admin.categories.edit', $category) }}" wire:navigate class="btn btn-ghost-secondary btn-sm">{{ __('messages.category') }}</a>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0 px-0 pb-0">
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="w-1">{{ __('messages.id') }}</th>
                                    <th>{{ __('messages.name') }}</th>
                                    <th>{{ __('messages.server_connection') }}</th>
                                    <th>{{ __('messages.slug') }}</th>
                                    <th>{{ __('messages.status') }}</th>
                                    <th class="text-end">Global</th>
                                    <th class="text-end">Client</th>
                                    <th>{{ __('messages.created_at') }}</th>
                                    <th class="w-1"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($category->packages as $package)
                                    <tr>
                                        <td class="text-secondary">{{ $package->id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center py-1">
                                                <span class="avatar avatar-sm rounded me-2 flex-shrink-0" style="background-image: url({{ $package->icon() }}); background-size: contain;"></span>
                                                <div class="min-w-0">
                                                    <a href="{{ route('admin.packages.edit', $package) }}" wire:navigate class="fw-semibold text-reset d-block text-truncate">{{ $package->name }}</a>
                                                    @if ($package->short_description)
                                                        <div class="text-secondary small text-truncate" style="max-width: 280px;">{{ Str::limit($package->short_description, 48) }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($package->serverConnection)
                                                <a href="{{ route('admin.servers.connections.edit', $package->serverConnection) }}" wire:navigate class="text-reset">{{ $package->serverConnection->alias }}</a>
                                            @else
                                                <span class="text-secondary">—</span>
                                            @endif
                                        </td>
                                        <td class="small">
                                            <a href="{{ route('packages.view', $package->slug) }}" target="_blank" rel="noopener" class="text-break">{{ $package->slug }}</a>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ match ($package->status) {
                                                'active' => 'success',
                                                'unlisted' => 'info',
                                                'restricted' => 'warning',
                                                default => 'secondary',
                                            } }}-lt">{{ ucfirst($package->status) }}</span>
                                        </td>
                                        <td class="text-end">
                                            @if ($package->global_quantity === -1)
                                                <span class="text-secondary">∞</span>
                                            @elseif ($package->global_quantity === 0)
                                                <span class="badge bg-danger-lt">Out of stock</span>
                                            @else
                                                <span class="text-secondary">{{ number_format($package->global_quantity) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if ($package->client_quantity === -1)
                                                <span class="text-secondary">∞</span>
                                            @elseif ($package->client_quantity === 0)
                                                <span class="badge bg-danger-lt">Out of stock</span>
                                            @else
                                                <span class="text-secondary">{{ number_format($package->client_quantity) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-secondary small">{{ $package->created_at->translatedFormat('d M Y') }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.packages.edit', $package) }}" wire:navigate class="btn btn-ghost-primary btn-sm">{{ __('messages.edit') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach

        @if ($packagesWithoutCategory->isNotEmpty())
            <div class="card mb-3 shadow-sm">
                <div class="card-header border-0 py-3 px-3 bg-transparent">
                    <div class="row align-items-center flex-fill">
                        <div class="col-auto">
                            <span class="avatar avatar-md rounded bg-warning-lt">
                                <i class="ti ti-alert-triangle text-warning"></i>
                            </span>
                        </div>
                        <div class="col min-w-0">
                            <h2 class="card-title mb-0 d-flex align-items-center gap-2">
                                <span>Packages without a category</span>
                                <span class="badge bg-warning-lt text-warning">Needs attention</span>
                            </h2>
                            <div class="text-secondary small">These packages need a valid category assignment.</div>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0 px-0 pb-0">
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="w-1">{{ __('messages.id') }}</th>
                                    <th>{{ __('messages.name') }}</th>
                                    <th>{{ __('messages.server_connection') }}</th>
                                    <th>{{ __('messages.slug') }}</th>
                                    <th>{{ __('messages.status') }}</th>
                                    <th class="text-end">Global</th>
                                    <th class="text-end">Client</th>
                                    <th>{{ __('messages.created_at') }}</th>
                                    <th class="w-1"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($packagesWithoutCategory as $package)
                                    <tr>
                                        <td class="text-secondary">{{ $package->id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center py-1">
                                                <span class="avatar avatar-sm rounded me-2 flex-shrink-0" style="background-image: url({{ $package->icon() }}); background-size: contain;"></span>
                                                <div class="min-w-0">
                                                    <a href="{{ route('admin.packages.edit', $package) }}" wire:navigate class="fw-semibold text-reset d-block text-truncate">{{ $package->name }}</a>
                                                    @if ($package->short_description)
                                                        <div class="text-secondary small text-truncate" style="max-width: 280px;">{{ Str::limit($package->short_description, 48) }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($package->serverConnection)
                                                <a href="{{ route('admin.servers.connections.edit', $package->serverConnection) }}" wire:navigate class="text-reset">{{ $package->serverConnection->alias }}</a>
                                            @else
                                                <span class="text-secondary">—</span>
                                            @endif
                                        </td>
                                        <td class="small">
                                            <a href="{{ route('packages.view', $package->slug) }}" target="_blank" rel="noopener" class="text-break">{{ $package->slug }}</a>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ match ($package->status) {
                                                'active' => 'success',
                                                'unlisted' => 'info',
                                                'restricted' => 'warning',
                                                default => 'secondary',
                                            } }}-lt">{{ ucfirst($package->status) }}</span>
                                        </td>
                                        <td class="text-end">
                                            @if ($package->global_quantity === -1)
                                                <span class="text-secondary">∞</span>
                                            @elseif ($package->global_quantity === 0)
                                                <span class="badge bg-danger-lt">Out of stock</span>
                                            @else
                                                <span class="text-secondary">{{ number_format($package->global_quantity) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if ($package->client_quantity === -1)
                                                <span class="text-secondary">∞</span>
                                            @elseif ($package->client_quantity === 0)
                                                <span class="badge bg-danger-lt">Out of stock</span>
                                            @else
                                                <span class="text-secondary">{{ number_format($package->client_quantity) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-secondary small">{{ $package->created_at->translatedFormat('d M Y') }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.packages.edit', $package) }}" wire:navigate class="btn btn-ghost-primary btn-sm">{{ __('messages.edit') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    @endif
@endsection
