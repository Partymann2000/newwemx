@extends('admin::layouts.wrapper', [
    'activePage' => 'subscriptions',
])

@section('title', 'Subscriptions')

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <ul class="nav nav-pills">
                @foreach(['active', 'cancelled', 'inactive'] as $status)
                    <li class="nav-item">
                        <a class="nav-link @if(request()->get('status', 'active') == $status) active @endif" aria-current="page" href="{{ route('admin.subscriptions.index', ['status' => $status]) }}" wire:navigate>{{ ucfirst($status) }} {{ ($status == 'active') ? \App\Models\Subscription::query()->whereStatus('active')->count() : \App\Models\Subscription::query()->whereStatus($status)->count() }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{--  Subscriptions Table  --}}
    @livewire(admin_view_path('livewire.table'), [
        'title' => 'Subscriptions',
        'entries' => 15,
        'columns' => [
            'ID',
            'Description',
            'Gateway',
            'Subscription ID',
            'Cycle',
            'Currency',
            'status',
            'Last Checked At',
            'Next Billing At',
            'Activated At',
            'Actions',
        ],
        'sortableColumns' => [],
        'rows' => \App\Models\Subscription::where('status', request()->get('status', 'active'))->latest('activated_at')->get()->map(function ($subscription) {
            return [
                $subscription->id,
                '<a href="' . route('admin.subscriptions.edit', $subscription->id) . '" wire:navigate>' . $subscription->description . '</a>',
                $subscription->gatewayConfig ? $subscription->gatewayConfig->display_name : '<span class="badge bg-secondary-lt">None</span>',
                Str::limit($subscription->subscription_id, 32),
                priceIn($subscription->amount, $subscription->currency) . ' / ' . daysToPeriod($subscription->frequency),
                strtoupper($subscription->currency),
                '<span class="badge bg-'. ($subscription->status == 'active' ? 'success' : 'danger') .'-lt">'. ucfirst($subscription->status) .'</span>',
                $subscription->last_checked_at ? $subscription->last_checked_at->format('d M, Y') : '-',
                $subscription->next_billing_at ? $subscription->next_billing_at->format('d M, Y') : '-',
                $subscription->activated_at ? $subscription->activated_at->format('d M, Y') : '-',
                '<a href="' . route('admin.subscriptions.edit', $subscription->id) . '" wire:navigate>Manage</a>'
            ];
        })->toArray(),
    ])
@endsection
