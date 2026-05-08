@extends('admin::layouts.wrapper', [
    'activePage' => 'currencies',
])

@section('title', __('messages.currencies'))

@section('actions')
    <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">
            <x-admin::button href="{{ route('admin.currencies.update-rates') }}" wire:navigate>{{ __('messages.update_rates') }}</x-admin::button>
            <x-admin::button href="{{ route('admin.currencies.create') }}" wire:navigate>{{ __('messages.create') }}</x-admin::button>
        </div>
    </div>
@endsection

@section('content')
    {{--  Currencies Table  --}}

    @livewire(admin_view_path('livewire.table'), [
    'title' => __('messages.currencies'),
    'entries' => 15,
    'columns' => [
        __('messages.currency'),
        __('messages.name'),
        __('messages.usd_rate'),
        __('messages.rate_updated_at'),
        __('messages.status'),
        __('messages.created_at'),
        '',
    ],
    'sortableColumns' => [
        __('messages.currency'),
        __('messages.name'),
        __('messages.usd_rate'),
        __('messages.rate_updated_at'),
        __('messages.status'),
        __('messages.created_at'),
    ],
    'rows' => \App\Models\Currency::latest()->get()->map(function ($currency) {
        return [
            '<a href="' . route('admin.currencies.edit', $currency->currency) . '" wire:navigate>' . $currency->currency . '</a>',
            $currency->display_name,
            (!$currency->previous_rate || $currency->previous_rate > $currency->getRate()) ? '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-trending-up text-green mr-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 17l6 -6l4 4l8 -8" /><path d="M14 7l7 0l0 7" /></svg> '. $currency->getRate() . ' '. $currency->currency : ($currency->previous_rate < $currency->getRate() ? '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-trending-down text-red"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7l6 6l4 -4l8 8" /><path d="M21 10l0 7l-7 0" /></svg>' : '') . ' ' . $currency->getRate() . ' ' . $currency->currency . ($currency->use_manual_rate ? ' <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-edit"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>' : ''),
            $currency->rateLastUpdatedAt(),
            $currency->isActive() ? '<span class="badge badge-outline text-green">Active</span>' : '<span class="badge badge-outline text-red">Inactive</span>',
            $currency->created_at->translatedFormat('d M Y'),
            '<a href="' . route('admin.currencies.edit', $currency->currency) . '" wire:navigate>' . __('messages.edit') . '</a>'
        ];
    })->toArray(),
])
@endsection
