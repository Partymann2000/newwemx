<?php

use App\Models\Package;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;

new class extends Component
{
    public Package $package;

    #[Url]
    public $packageEditPage = 'general';

    public $pages = [];

    public function mount(Package $package)
    {
        $this->package = $package;

        $this->pages = [
            'general' => [
                'name' => 'General',
                'component' => admin_view_path('packages.livewire.edit.general'),
            ],
            'features' => [
                'name' => 'Features',
                'component' => admin_view_path('packages.livewire.edit.features'),
            ],
            'prices' => [
                'name' => 'Prices',
                'component' => admin_view_path('packages.livewire.edit.prices.index'),
            ],
            'create_price' => [
                'name' => 'Create Price',
                'component' => admin_view_path('packages.livewire.edit.prices.create'),
                'visible' => false,
            ],
            'edit_price' => [
                'name' => 'Edit Price',
                'component' => admin_view_path('packages.livewire.edit.prices.edit'),
                'visible' => false,
            ],
            'server' => [
                'name' => 'Server',
                'component' => admin_view_path('packages.livewire.edit.server-config'),
            ],
            'config_options' => [
                'name' => 'Configurable Options',
                'component' => admin_view_path('packages.livewire.edit.config-options'),
            ],
        ];
    }

    public function setPage($packageEditPage)
    {
        $this->packageEditPage = $packageEditPage;
    }
}
?>

<div>
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs nav-fill" data-bs-toggle="tabs" role="tablist">
                    @foreach($pages as $pageName => $pageData)
                        @if(array_key_exists('visible', $pageData) && !$pageData['visible'])
                            @continue
                        @endif
                    <li class="nav-item act" role="presentation">
                        <a href="{{ route('admin.packages.edit', ['package' => $package->id, 'packageEditPage' => $pageName]) }}" wire:navigate class="nav-link {{ when($packageEditPage == $pageName, 'active') }} ">{{ $pageData['name'] }}</a>
                    </li>
                    @endforeach

                </ul>
            </div>
            <div class="card-body">
                @foreach($pages as $pageName => $pageData)
                    @if($packageEditPage == $pageName)
                        @livewire($pageData['component'], ['package' => $package])
                    @endif
                @endforeach

                @if(!array_key_exists($packageEditPage, $pages))
                    <div class="empty">
                        <div class="empty-header">404</div>
                        <p class="empty-title">Oops… You just found an empty page</p>
                        <p class="empty-subtitle text-secondary">
                            We are sorry but the page you are looking for was not found
                        </p>
                        <div class="empty-action">
                            <button type="button" wire:click="setPage('general')" class="btn btn-primary">
                                Take me home
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
