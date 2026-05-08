<?php

use Livewire\Volt\Component;
use Illuminate\View\View;

new class extends Component {

    public $package;

    public function mount($package): void
    {
        $this->package = $package;
    }
}
?>

<div>
    <div class="d-flex justify-content-end">
        <x-admin::button href="{{ route('admin.packages.edit', ['package' => $package->id, 'packageEditPage' => 'create_price']) }}" wire:navigate>
            {{ __('messages.create_price') }}
        </x-admin::button>
    </div>

    @livewire(admin_view_path('livewire.table'), [
        'class' => '',
        'title' => __('messages.prices'),
        'entries' => 8,
        'columns' => [
            __('messages.id'),
            'Price',
            'Setup Fee',
            'Upgrade Fee',
            __('messages.created_at'),
            '',
        ],
        'sortableColumns' => [
            __('messages.id'),
            'Price',
            'Setup Fee',
            'Upgrade Fee',
            __('messages.created_at'),
        ],
        'rows' => $package->prices()->get()->map(function ($price) {
            return [
                $price->id,
                '<a href="' . route('admin.packages.edit', ['package' => $price->package_id, 'packageEditPage' => 'edit_price', 'priceId' => $price->id]) . '" wire:navigate>' . price($price->price, settings('currency', 'USD')) . ' / ' . $price->cycle() . '</a>',
                price($price->setup_fee, settings('currency', 'USD')),
                price($price->upgrade_fee, settings('currency', 'USD')),
                $price->created_at->translatedFormat('d M Y'),
                '<a href="' . route('admin.packages.edit', ['package' => $price->package_id, 'packageEditPage' => 'edit_price', 'priceId' => $price->id]) . '" wire:navigate>' . __('messages.edit') . '</a>'
            ];
        })->toArray(),
    ])
</div>
