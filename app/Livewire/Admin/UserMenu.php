<?php

namespace App\Livewire\Admin;

use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Component;
use Auth;

class UserMenu extends Component
{
    public $user;
    public $menuItems = [];

    public function render(): Factory|View
    {
        return view('admin::components.navigation.user-menu');
    }

    /**
     * Component initialization.
     */
    public function mount(): void
    {
        $this->user = Auth::user();
        $this->initializeMenuItems();
    }

    /**
     * Initialization of menu items.
     */
    private function initializeMenuItems(): void
    {
        $this->menuItems = [
            [
                'title' => __('messages.status'),
                'href' => '#status',
            ],
            [
                'title' => __('messages.profile'),
                'href' => '#profile',
            ],
            [
                'title' => __('messages.settings'),
                'href' => '#settings',
            ],
        ];
        $this->menuItems = array_merge(
            $this->menuItems,
            $this->getExceptionsItems(),
            $this->getLogOutItem() // Add logout item at the end
        );
    }

    private function getExceptionsItems(): array
    {
        // TODO: Add your custom menu items here
        return [];
    }

    private function getLogOutItem(): array
    {
        return [
            [
                'divider' => true,
            ],
            [
                'title' => __('messages.logout'),
                'href' => route('logout'),
                'callable' => 'logout',
            ]
        ];
    }

    public function logout(): void
    {
        Auth::logout();
        redirect()->route('login');
    }


}
