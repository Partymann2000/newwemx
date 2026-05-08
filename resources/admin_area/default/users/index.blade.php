@extends('admin::layouts.wrapper', [
    'activePage' => 'users'
])

@section('title', __('messages.customers'))

@section('actions')
{{--    @component('admin::components.form-modal', ['action' => \App\Actions\Users\CreateUser::class]) @endcomponent--}}
<div class="col-auto ms-auto d-print-none">
    <div class="btn-list">
        <x-admin::button href="{{ route('admin.users.create') }}" wire:navigate>{{ __('messages.create') }}</x-admin::button>
    </div>
</div>
@endsection

@section('content')
    {{--  Users Table  --}}
    @livewire(admin_view_path('livewire.table'), [
        'title' => __('messages.customers'),
        'entries' => 15,
        'columns' => [
            __('messages.id'),
            __('messages.name'),
            __('messages.email'),
            __('messages.status'),
            __('messages.balance'),
            __('messages.language'),
            __('messages.last_seen_at'),
            __('messages.created_at'),
            '',
        ],
        'sortableColumns' => [
            __('messages.id'),
            __('messages.name'),
            __('messages.email'),
            __('messages.status'),
            __('messages.balance'),
            __('messages.language'),
            __('messages.last_seen_at'),
            __('messages.created_at'),
        ],
        'rows' => \App\Models\User::latest()->get()->map(function ($user) {
            return [
                $user->id,
                '<div class="d-flex align-items-center">
                    <span class="avatar" style="background-image: url(' . $user->getAvatarUrl() . '); margin-right: 10px;"></span>
                    <div class="d-flex flex-column">
                        <a href="' . route('admin.users.edit', $user->id) . '" wire:navigate>'
                            . $user->username
                            . ($user->isStaff()
                                ? '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="icon text-yellow ms-1"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8.243 7.34l-6.38 .925l-.113 .023a1 1 0 0 0 -.44 1.684l4.622 4.499l-1.09 6.355l-.013 .11a1 1 0 0 0 1.464 .944l5.706 -3l5.693 3l.1 .046a1 1 0 0 0 1.352 -1.1l-1.091 -6.355l4.624 -4.5l.078 -.085a1 1 0 0 0 -.633 -1.62l-6.38 -.926l-2.852 -5.78a1 1 0 0 0 -1.794 0l-2.853 5.78z"/></svg>'
                                : '')
                            . '</a>'
                            . $user->full_name .
                        '</div>
                    </div>',
                $user->email,
                '<span class="badge bg-' . ($user->status == 'active' ? 'success' : 'danger') . '-lt text-white">' . ucfirst($user->status) . '</span>',
                priceIn($user->balance, baseCurrency()),
                '<div class="d-flex align-items-center"><span class="flag flag-xxs flag-country-' . $user->language()->flag . ' me-1"></span> ' . ($user->language()->name ?? 'N/A') . ' (' . strtoupper($user->language) . ')</div>',
                $user->last_seen_at ? $user->last_seen_at->diffForHumans() : '-',
                $user->created_at->translatedFormat('d M Y'),
                '<a href="' . route('admin.users.edit', $user->id) . '" wire:navigate>' . __('messages.edit') . '</a>'
            ];
        })->toArray(),
    ])
@endsection
