@extends('theme::dashboard.dashboard-layout')

@section('container')
    <div class="mb-4">
        @livewire(client_view_path('livewire.table'), [
            'title' => 'Pending Invites',
            'description' => 'View all your pending invites to orders.',
            'columns' => [
                'Order',
                'Invited By',
                'Status',
                'Invited On',
                'Actions',
            ],
            'rows' =>
                \App\Models\OrderMember::where('email', auth()->user()->email)->latest()->get()->map(function($invite) {
                    return [
                        $invite->order->package->name . ' (#' . $invite->order->id . ')',
                        $invite->order->user->username,
                        ucfirst($invite->status),
                        $invite->created_at->format(settings('date_format', 'd M Y H:i')),
                        $invite->status == 'pending' ? '<a href="'. route('orders.invites.accept', ['member_id' => $invite->id]) .'" class="text-blue-600 dark:text-blue-500 hover:underline" wire:navigate>Accept</a> '. '<a href="'. route('orders.invites.reject', ['member_id' => $invite->id]) .'" class="text-red-600 dark:text-red-500 hover:underline ms-2" wire:navigate>Reject</a>' : '<a href="'. route('orders.invites.reject', ['member_id' => $invite->id]) .'" class="text-red-600 dark:text-red-500 hover:underline" wire:navigate>Reject</a>',
                    ];
                })->toArray(),
        ])
    </div>

{{--    <x-theme::table containerClass="mb-4">--}}
{{--        <x-theme::table.caption--}}
{{--            title="Pending Invites"--}}
{{--            description="View all your pending invites to orders."--}}
{{--        />--}}

{{--        <x-theme::table.head>--}}
{{--            <x-theme::table.head-cell>Order</x-theme::table.head-cell>--}}
{{--            <x-theme::table.head-cell>Invited By</x-theme::table.head-cell>--}}
{{--            <x-theme::table.head-cell>Status</x-theme::table.head-cell>--}}
{{--            <x-theme::table.head-cell>Invited On</x-theme::table.head-cell>--}}
{{--            <x-theme::table.head-cell>Actions</x-theme::table.head-cell>--}}
{{--        </x-theme::table.head>--}}

{{--        <x-theme::table.body>--}}
{{--            @foreach(\App\Models\OrderMember::where('email', auth()->user()->email)->latest()->get() as $invite)--}}
{{--                <x-theme::table.row>--}}
{{--                    <x-theme::table.row-header>--}}
{{--                        {{ $invite->order->package->name }} (#{{ $invite->order->id }})--}}
{{--                    </x-theme::table.row-header>--}}
{{--                    <x-theme::table.cell>--}}
{{--                        {{ $invite->order->user->username }}--}}
{{--                    </x-theme::table.cell>--}}
{{--                    <x-theme::table.cell>--}}
{{--                        {{ ucfirst($invite->status) }}--}}
{{--                    </x-theme::table.cell>--}}
{{--                    <x-theme::table.cell>--}}
{{--                        {{ $invite->created_at->format(settings('date_format', 'd M Y H:i')) }}--}}
{{--                    </x-theme::table.cell>--}}
{{--                    <x-theme::table.cell>--}}
{{--                        @if($invite->status == 'pending')--}}
{{--                            <a href="{{ route('orders.invites.accept', ['member_id' => $invite->id]) }}" class="text-blue-600 dark:text-blue-500 hover:underline" wire:navigate>Accept</a>--}}
{{--                            <a href="{{ route('orders.invites.reject', ['member_id' => $invite->id]) }}" class="text-red-600 dark:text-red-500 hover:underline ms-2" wire:navigate>Reject</a>--}}
{{--                        @else--}}
{{--                            <a href="{{ route('orders.invites.reject', ['member_id' => $invite->id]) }}" class="text-red-600 dark:text-red-500 hover:underline" wire:navigate>Reject</a>--}}
{{--                        @endif--}}
{{--                    </x-theme::table.cell>--}}
{{--                </x-theme::table.row>--}}
{{--            @endforeach--}}
{{--        </x-theme::table.body>--}}
{{--    </x-theme::table>--}}
@endsection
