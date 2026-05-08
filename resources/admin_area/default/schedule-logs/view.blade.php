@extends('admin::layouts.wrapper', [
    'activePage' => 'schedule-logs',
])

@section('title', 'Viewing log #'. $log->id)

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <h4>Event</h4>
            <div>
                <pre><code>{{ $log->task }}</code></pre>
            </div>
            <h4>Description</h4>
            <div>
                <pre><code>{{ $log->message }}</code></pre>
            </div>
            <h4>Status</h4>
            <div>
                <pre><code>{{ ucfirst($log->status) }}</code></pre>
            </div>
            <h4>Date</h4>
            <div>
                <pre>{{ $log->created_at->format('Y-m-d H:i:s') }}</pre>
            </div>
            @if($log->data)
            <h4>Data</h4>
            <div>
                <pre><code>{{ json_encode($log->data, JSON_PRETTY_PRINT) }}</code></pre>
            </div>
            @endif
        </div>
    </div>

    @php
        // if log data contains orders array with order IDs, we can show related orders
        $orderQuery = \App\Models\Order::query();
        if (is_array($log->data) && isset($log->data['orders'])
            && is_array($log->data['orders']) && count($log->data['orders']) > 0) {
            $orderQuery->whereIn('id', $log->data['orders']);
        } else {
            $orderQuery->whereRaw('1 = 0'); // no orders
        }

        $orderQuery = $orderQuery->latest();
    @endphp

    @if($orderQuery->count() > 0)
    {{--  Orders Table  --}}
    @livewire(admin_view_path('livewire.table'), [
        'title' => 'Affected Orders',
        'entries' => 15,
        'columns' => [
            __('messages.id'),
            __('messages.name'),
            __('messages.package'),
            __('messages.user'),
            __('messages.amount'),
            __('messages.status'),
            __('messages.created_at'),
            '',
        ],
        'sortableColumns' => [
            __('messages.id'),
            __('messages.name'),
            __('messages.amount'),
            __('messages.status'),
            __('messages.created_at'),
        ],
        'rows' => $orderQuery->get()->map(function ($order) {
            return [
                $order->id,
                '<a href="' . route('admin.orders.edit', $order->id) . '" wire:navigate>' . $order->package->name . '</a>',
                '<div class="d-flex py-1 align-items-center"><img src="' . $order->package->icon() . '" class="avatar me-2" alt="' . $order->package->name . '"><div class="flex-fill"><div class="font-weight-medium"><a href="' . route('admin.packages.edit', $order->package_id) . '" wire:navigate class="text-reset">' . $order->package->name . '</a></div><div class="text-secondary"><a href="' . route('admin.categories.edit', $order->package->category_id) . '" wire:navigate class="text-reset">' . $order->package->category->name . '</a></div></div></div>',
                $order->user ? '<div class="d-flex py-1 align-items-center"><span class="avatar avatar-2 me-2" style="background-image: url(' . $order->user->getAvatarUrl() . ')"></span><div class="flex-fill"><div class="font-weight-medium"><a href="' . route('admin.users.edit', $order->user_id) . '" wire:navigate class="text-reset">' . $order->user->full_name . ' (' . $order->user->username . ')</a></div><div class="text-secondary"><a href="'. route('admin.users.edit', $order->user_id) .'" wire:navigate class="text-reset">' . $order->user->email . '</a></div></div></div>' : '<span class="badge bg-secondary-lt">Guest</span>',
                price($order->price) . ' / ' . $order->cycle(),
                $order->status == 'active' ? '<span class="badge bg-green-lt">Active</span>' : ($order->status == 'suspended' ? '<span class="badge bg-warning-lt">Suspended</span>' : ($order->status == 'terminated' ? '<span class="badge bg-danger-lt">Terminated</span>' : '<span class="badge bg-warning-lt">' . ucfirst($order->status) . '</span>')),
                $order->created_at->translatedFormat('d M Y H:i'),
                '<a href="' . route('admin.orders.edit', $order->id) . '" wire:navigate>' . __('messages.edit') . '</a>'
            ];
        })->toArray(),
    ])
    @endif
@endsection
