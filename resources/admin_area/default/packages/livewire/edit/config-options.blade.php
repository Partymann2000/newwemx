<?php

use App\Models\Package;
use Livewire\Volt\Component;
use Livewire\Attributes\On;

new class extends Component
{
    public $package;

    public function mount(Package $package): void
    {
        $this->package = $package;

        $this->config = $package->data ?? [];
    }

    #[On('config-options-updated')]
    public function refreshConfigOptions(): void
    {
        $this->package->refresh();
        $this->package->load('configOptions');
    }
}
?>

<div>
    <div class="d-flex justify-content-end align-items-center">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create-config-option">
            Create Config Option
        </button>
    </div>

    @livewire(admin_view_path('livewire.table'), [
        'title' => 'Config Options',
        'columns' => [
            'key',
            'label',
            'type',
            'date',
            '',
        ],
        'rows' => $package->configOptions->map(function ($option) {
            return [
                'key' => $option->key,
                'label' => $option->label,
                'type' => $option->type,
                'date' => $option->created_at->format('Y-m-d H:i:s'),
                '<a href="#" data-bs-toggle="modal" data-bs-target="#edit-config-option-'. $option->id .'">Edit</a>',
            ];
        })->toArray(),
        'sortableColumns' => [
            'key',
            'label',
            'type',
            'date',
        ],
        'class' => '',
    ])

    @foreach($package->configOptions as $option)
        @livewire(admin_view_path('packages.livewire.edit.config_options.edit-config-option-modal'), ['configOption' => $option])
    @endforeach

    @livewire(admin_view_path('packages.livewire.edit.config_options.create-config-option-modal'), ['package' => $package])
</div>
