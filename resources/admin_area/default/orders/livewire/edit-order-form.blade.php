<?php

use Livewire\Volt\Component;
use App\Models\Order;
use Livewire\Attributes\Url;

new class extends Component
{
    public $order;

    #[Url('orderEditPage')]
    public $activePage = 'general';

    public $pages = [];

    public function mount(Order $order)
    {
        $this->order = $order;

        $this->pages = [
            'general' => [
                'title' => 'General',
                'livewire' => admin_view_path('orders.livewire.edit.general'),
            ],
            'prices' => [
                'title' => 'Prices',
                'livewire' => admin_view_path('orders.livewire.edit.prices'),
            ],
            'payments' => [
                'title' => 'Payments',
                'livewire' => admin_view_path('orders.livewire.edit.payments'),
            ],
            'email-history' => [
                'title' => 'Email History',
                'livewire' => admin_view_path('orders.livewire.edit.email-history'),
            ],
            'server' => [
                'title' => 'Server',
                'livewire' => admin_view_path('orders.livewire.edit.server'),
            ],
            'incident-logs' => [
                'title' => 'Incident Logs',
                'livewire' => admin_view_path('orders.livewire.edit.incident-logs'),
            ],
            'status-logs' => [
                'title' => 'Status Logs',
                'livewire' => admin_view_path('orders.livewire.edit.status-logs'),
            ],
            'changelogs' => [
                'title' => 'Changelogs',
                'livewire' => admin_view_path('orders.livewire.edit.changelogs'),
            ],
        ];
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
                <a href="{{ route('admin.orders.edit', ['order' => $order->id, 'orderEditPage' => $key]) }}" wire:navigate class="nav-link @if($activePage == $key) active @endif">{{ $page['title'] }}</a>
            </li>
            @endforeach
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">

            <div class="tab-pane active show" id="tabs-user-general" role="tabpanel">
                @if(in_array($activePage, array_keys($pages)))
                    @livewire($pages[$activePage]['livewire'], ['order' => $order])
                @else
                    <div class="alert alert-danger" role="alert">
                        Page not found
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

