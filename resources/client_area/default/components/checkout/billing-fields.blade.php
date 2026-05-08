@props([
    'companyName' => null,
    'country' => null,
])

<div>
    <div class="mb-4">
        <x-theme::form.label for="company_name" text="Company Name (optional)" />
        <x-theme::form.input type="text" id="company_name" wire:model.change="company_name" placeholder="Company Name" />
        @error('company_name')
            <x-theme::form.error :text="$message" />
        @enderror
    </div>

    @if($companyName)
        <div class="mb-4">
            <x-theme::form.label for="tax_id" text="Tax ID (optional)" />
            <x-theme::form.input type="text" id="tax_id" wire:model.change="tax_id" placeholder="Tax ID" />
            @error('tax_id')
                <x-theme::form.error :text="$message" />
            @enderror
        </div>
    @endif

    <div class="mb-4">
        <x-theme::form.label for="country" text="Country" />
        <x-theme::form.select id="country" wire:model.change="country" :options="\App\Facades\World::countries()" placeholder="Select Country" />
        @error('country')
            <x-theme::form.error :text="$message" />
        @enderror
    </div>

    @if(in_array(strtoupper((string) $country), ['US', 'CA'], true))
        <div class="mb-4">
            <x-theme::form.label for="region" text="State" />
            <x-theme::form.select id="region" wire:model.change="region" :options="\App\Facades\World::states($country)" />
            @error('region')
                <x-theme::form.error :text="$message" />
            @enderror
        </div>
    @endif

    <div>
        <x-theme::form.label for="zip_code" text="ZIP Code" />
        <x-theme::form.input type="text" id="zip_code" wire:model="zip_code" placeholder="Zip Code" />
        @error('zip_code')
            <x-theme::form.error :text="$message" />
        @enderror
    </div>
</div>
