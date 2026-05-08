@extends('admin::layouts.wrapper', [
    'activePage' => 'payments',
])

@section('title', __('messages.payments'))

@section('actions')
    <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">
            <x-admin::button href="{{ route('admin.payments.create') }}" wire:navigate>{{ __('messages.create') }}</x-admin::button>
        </div>
    </div>
@endsection

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <ul class="nav nav-pills">
            @foreach(['paid', 'unpaid', 'refunded'] as $status)
            <li class="nav-item">
                <a class="nav-link @if(request()->get('status', 'paid') == $status) active @endif" aria-current="page" href="{{ route('admin.payments.index', ['status' => $status]) }}" wire:navigate>{{ ucfirst($status) }} {{ \App\Models\Payment::whereStatus($status)->count() }}</a>
            </li>
            @endforeach
        </ul>
    </div>
</div>

{{--  Payments Table  --}}
@livewire(admin_view_path('livewire.table'), [
    'title' => __('messages.payments'),
    'entries' => 15,
    'columns' => [
        __('messages.id'),
        __('messages.invoice_id'),
        __('messages.description'),
        __('messages.user'),
        __('messages.amount'),
        __('messages.currency'),
        __('messages.status'),
        __('messages.gateway'),
        __('messages.created_at'),
        '',
    ],
    'sortableColumns' => [
        __('messages.id'),
        __('messages.invoice_id'),
        __('messages.description'),
        __('messages.amount'),
        __('messages.currency'),
        __('messages.status'),
        __('messages.gateway'),
        __('messages.created_at'),
    ],
    'rows' => \App\Models\Payment::query()->where('status', request()->get('status', 'paid'))->latest()->get()->map(function ($payment) {
        return [
            $payment->id,
            $payment->invoice_id,
            '<a href="' . route('admin.payments.edit', $payment->id) . '" wire:navigate>' . $payment->description . '</a>',
            $payment->user ? '<div class="d-flex py-1 align-items-center"><span class="avatar avatar-2 me-2" style="background-image: url(' . $payment->user->getAvatarUrl() . ')"></span><div class="flex-fill"><div class="font-weight-medium"><a href="' . route('admin.users.edit', $payment->user_id) . '" wire:navigate class="text-reset">' . $payment->user->full_name . ' (' . $payment->user->username . ')</a></div><div class="text-secondary"><a href="'. route('admin.users.edit', $payment->user_id) .'" wire:navigate class="text-reset">' . $payment->user->email . '</a></div></div></div>' : '<span class="badge bg-secondary-lt">Guest</span>',
            priceIn($payment->total(), $payment->currency),
            $payment->currency,
            $payment->status == 'paid' ? '<span class="badge bg-green-lt">Paid</span>' : ($payment->status == 'unpaid' ? '<span class="badge bg-danger-lt">Unpaid</span>' : '<span class="badge bg-info-lt">' . ucfirst($payment->status) . '</span>'),
            $payment->gatewayConfig ? $payment->gatewayConfig->display_name : '<span class="badge bg-secondary-lt">None</span>',
            $payment->created_at->translatedFormat('d M Y H:i'),
            '<a href="' . route('admin.payments.edit', $payment->id) . '" wire:navigate>' . __('messages.edit') . '</a>'
        ];
    })->toArray(),
])
@endsection
