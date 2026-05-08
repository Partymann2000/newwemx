<?php

use App\Models\Package;
use Livewire\Volt\Component;

new class extends Component
{
    public $package;

    public $description = '';

    public function mount(Package $package): void
    {
        $this->package = $package;
    }

    public function addFeature(): void
    {
        Package::actions()->createFeatureAsAdmin([
            'package_id' => $this->package->id,
            'description' => $this->description,
        ]);

        $this->redirect(route('admin.packages.edit', ['package' => $this->package->id, 'packageEditPage' => 'features']), true);
    }

    public function updateFeature($featureId): void
    {
        Package::actions()->updateFeatureAsAdmin([
            'feature_id' => $featureId,
            'description' => $this->description,
        ]);

        $this->redirect(route('admin.packages.edit', ['package' => $this->package->id, 'packageEditPage' => 'features']), true);
    }

    public function deleteFeature($featureId): void
    {
        Package::actions()->deleteFeatureAsAdmin([
            'feature_id' => $featureId,
        ]);

        $this->redirect(route('admin.packages.edit', ['package' => $this->package->id, 'packageEditPage' => 'features']), true);
    }
}
?>

<div>
    <div class="text-end mb-3">
        <x-admin::button label="Create Feature" color="primary" data-bs-toggle="modal" data-bs-target="#add-feature-modal" wire:click="$set('description', '')"/>
    </div>
    <table class="table table-responsive">
        <thead>
        <tr>
            <th class="text-nowrap">Description</th>
            <th class="text-nowrap">Created At</th>
            <th class="text-nowrap">Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($package->features as $feature)
            <tr>
                <td>{{ $feature->description }}</td>
                <td>{{ $feature->created_at->translatedFormat('d M Y') }}</td>
                <td>
                    <x-admin::button label="Edit" data-bs-toggle="modal" data-bs-target="#add-feature-modal-{{ $feature->id }}" wire:click="$set('description', '{{ $feature->description }}')"/>
                    <x-admin::button label="Delete" color="danger" wire:click="deleteFeature({{ $feature->id }})"/>
                </td>
            </tr>
            <div class="modal modal-blur fade" id="add-feature-modal-{{ $feature->id }}" tabindex="-1" role="dialog" aria-hidden="true" wire:ignore.self>
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">New Feature</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <x-admin::form.label for="description" label="Description"/>
                                <x-admin::form.input type="text" id="description" wire:model="description" placeholder="Enter feature description"/>
                                @error('description')
                                <x-admin::form.error message="{{ $message }}"/>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="#" class="btn btn-link link-secondary" data-bs-dismiss="modal">
                                Cancel
                            </a>
                            <button type="button" class="btn btn-primary ms-auto" wire:click="updateFeature({{ $feature->id }})">
                                <!-- SVG Icon for Plus -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <path d="M12 5l0 14"></path>
                                    <path d="M5 12l14 0"></path>
                                </svg>
                                Update Feature
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        </tbody>
    </table>

    <div class="modal modal-blur fade" id="add-feature-modal" tabindex="-1" role="dialog" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Feature</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <x-admin::form.label for="description" label="Description"/>
                        <x-admin::form.input type="text" id="description" wire:model="description" placeholder="Enter feature description"/>
                        @error('description')
                        <x-admin::form.error message="{{ $message }}"/>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-link link-secondary" data-bs-dismiss="modal">
                        Cancel
                    </a>
                    <button type="button" class="btn btn-primary ms-auto" wire:click="addFeature()">
                        <!-- SVG Icon for Plus -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M12 5l0 14"></path>
                            <path d="M5 12l14 0"></path>
                        </svg>
                        Add Feature
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
