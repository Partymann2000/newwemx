<?php

use Livewire\Volt\Component;
use App\Models\User;
use Livewire\Attributes\Url;

new class extends Component
{
    public $user;

    #[Url('userEditPage')]
    public $activePage = 'general';

    public $pages = [];

    public function mount(User $user)
    {
        $this->user = $user;

        $extensionPages = collect(extensionElements('admin-customer-edit-menu-item'))->mapWithKeys(function ($item) {
            return [
                $item['attributes']['identifier'] => [
                    'title' => $item['attributes']['name'],
                    'view' => $item['view'],
                    'show' => isset($item['attributes']['show']) ? $item['attributes']['show'] : true,
                ]
            ];
        })->toArray();

        $this->pages = [
            'general' => [
                'title' => 'General',
                'livewire' => admin_view_path('users.livewire.edit.general'),
            ],
            'address' => [
                'title' => 'Address',
                'livewire' => admin_view_path('users.livewire.edit.address'),
            ],
            'roles' => [
                'title' => 'Roles',
                'livewire' => admin_view_path('users.livewire.edit.roles'),
                'show' => auth()->user()->hasPerm('admin.users.manage_roles'),
            ],
            'email-history' => [
                'title' => 'Email History',
                'livewire' => admin_view_path('users.livewire.edit.email-history'),
            ],
            'alt-accounts' => [
                'title' => 'Alt Accounts',
                'livewire' => admin_view_path('users.livewire.edit.alt-accounts'),
            ],
            'activity' => [
                'title' => 'Activity',
                'livewire' => admin_view_path('users.livewire.edit.activity'),
            ],
            'moderation' => [
                'title' => 'Moderation',
                'livewire' => admin_view_path('users.livewire.edit.moderation'),
                'show' => auth()->user()->hasPerm('admin.users.update'),
            ],
        ];

        $this->pages = array_merge($this->pages, $extensionPages);
    }

}

?>

<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs nav-fill" data-bs-toggle="tabs" role="tablist">
            @foreach($pages as $key => $page)
                @if(isset($page['show']) && !$page['show'])
                    @continue
                @endif
            <li class="nav-item act" role="presentation">
                <a href="{{ route('admin.users.edit', ['user' => $user->id, 'userEditPage' => $key]) }}" wire:navigate class="nav-link @if($activePage == $key) active @endif">{{ $page['title'] }}</a>
            </li>
            @endforeach
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">

            <div class="tab-pane active show" id="tabs-user-general" role="tabpanel">
                @if(in_array($activePage, array_keys($pages)))
                    @if(isset($pages[$activePage]['livewire']))
                        @livewire($pages[$activePage]['livewire'], ['user' => $user])
                    @else
                        @include($pages[$activePage]['view'], ['user' => $user])
                    @endif
                @else
                    <div class="alert alert-danger" role="alert">
                        Page not found
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

