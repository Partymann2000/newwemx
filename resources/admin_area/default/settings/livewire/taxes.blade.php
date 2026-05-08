<?php

use App\Models\GatewayConfig;
use App\Facades\World;
use Livewire\Volt\Component;
use Illuminate\View\View;
use App\Models\Setting;

new class extends Component {
    public $enable_sales_tax = false;

    public $include_tax_in_price = false;

    public $enable_exempt_sales_tax = true;

    public $verify_vat_id = false;

    public $allow_client_pdf_invoices = false;

    public $excluded_tax_gateways = [];

    public $tax_non_exempt_countries = [];

    public function mount()
    {
        $this->enable_sales_tax = settings('enable_sales_tax', false) == '1' ? true : false;
        $this->include_tax_in_price = settings('include_tax_in_price', false) == '1' ? true : false;
        $this->enable_exempt_sales_tax = settings('enable_exempt_sales_tax', true) == '1' ? true : false;
        $this->verify_vat_id = settings('verify_vat_id', false) == '1' ? true : false;
        $this->allow_client_pdf_invoices = settings('allow_client_pdf_invoices', false) == '1' ? true : false;
        $this->excluded_tax_gateways = array_values(array_filter(explode(',', settings('excluded_tax_gateways', ''))));

        if (empty($this->excluded_tax_gateways)) {
            $this->excluded_tax_gateways = GatewayConfig::query()
                ->where('extension_identifier', 'gateway-balance')
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->toArray();
        }

        $this->tax_non_exempt_countries = array_values(array_filter(explode(',', settings('tax_non_exempt_countries', ''))));
    }

    public function saveChanges()
    {
        Setting::actions()->updateApplicationTaxSettingsAsAdmin([
            'enable_sales_tax' => $this->enable_sales_tax,
            'include_tax_in_price' => $this->include_tax_in_price,
            'enable_exempt_sales_tax' => $this->enable_exempt_sales_tax,
            'verify_vat_id' => $this->verify_vat_id,
            'allow_client_pdf_invoices' => $this->allow_client_pdf_invoices,
            'excluded_tax_gateways' => $this->excluded_tax_gateways,
            'tax_non_exempt_countries' => $this->tax_non_exempt_countries,
        ]);

    }
}

?>

<div>
    <x-admin::settings.page-form title="Tax Settings">

            <div class="mb-4">
                <h3 class="card-title">Enable Sales Tax</h3>
                <p class="card-subtitle">
                    Do you want to enable sales tax calculations in your application?
                </p>
                <div class="row g-2">
                    <div class="col-auto">
                        <div class="mb-2">
                            <label class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" type="checkbox" wire:model="enable_sales_tax">
                                <span class="form-check-label form-check-label">
                                    Enable
                                </span>
                            </label>
                        </div>
                    </div>
                    @error('enable_sales_tax')
                    <x-admin::form.error :message="$message"/>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <h3 class="card-title">Include Tax In Price</h3>
                <p class="card-subtitle">
                    If enabled, the sales tax will be included in the product price. If disabled, the sales tax will
                    be added to the product price at checkout.
                </p>
                <div class="row g-2">
                    <div class="col-auto">
                        <div class="mb-2">
                            <label class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" type="checkbox" wire:model="include_tax_in_price">
                                <span class="form-check-label form-check-label">
                                    Enable
                                </span>
                            </label>
                        </div>
                    </div>
                    @error('include_tax_in_price')
                        <x-admin::form.error :message="$message"/>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <h3 class="card-title">Enable Sales Tax Exemption</h3>
                <p class="card-subtitle">
                    Exempt sales tax for businesses that provide a company name and tax ID at checkout.
                </p>
                <div class="row g-2">
                    <div class="col-auto">
                        <div class="mb-2">
                            <label class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" type="checkbox" wire:model="enable_exempt_sales_tax">
                                <span class="form-check-label form-check-label">
                                    Enable
                                </span>
                            </label>
                        </div>
                    </div>
                    @error('enable_exempt_sales_tax')
                    <x-admin::form.error :message="$message"/>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <h3 class="card-title">Verify VAT ID</h3>
                <p class="card-subtitle">
                    If a user provides a European VAT ID, the application will make a request to the European
                    Commission's VIES service to verify the VAT ID. If the VAT ID is valid, the user will be exempt from
                    sales tax.
                </p>
                <div class="row g-2">
                    <div class="col-auto">
                        <div class="mb-2">
                            <label class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" type="checkbox" wire:model="verify_vat_id">
                                <span class="form-check-label form-check-label">
                                    Enable
                                </span>
                            </label>
                        </div>
                    </div>
                    @error('verify_vat_id')
                    <x-admin::form.error :message="$message"/>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <h3 class="card-title">Allow Client PDF Invoices</h3>
                <p class="card-subtitle">
                    If enabled, users can download invoice PDFs from the client area. Admins can always download PDFs.
                </p>
                <div class="row g-2">
                    <div class="col-auto">
                        <div class="mb-2">
                            <label class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" type="checkbox" wire:model="allow_client_pdf_invoices">
                                <span class="form-check-label form-check-label">
                                    Enable
                                </span>
                            </label>
                        </div>
                    </div>
                    @error('allow_client_pdf_invoices')
                    <x-admin::form.error :message="$message"/>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <h3 class="card-title">Exclude Gateways for Tax Calculation</h3>
                <p class="card-subtitle">
                    For which payment gateways do you want to exclude sales tax calculations? This is useful for
                    gateways that handle taxes themselves, such as Stripe.
                </p>
                <div class="row g-2">
                    <div class="col">
                        <x-admin::form.select wire:model="excluded_tax_gateways" id="excluded_tax_gateways"
                                              :options="GatewayConfig::pluck('display_name', 'id')->toArray()"
                                              multiple/>
                    </div>
                    @error('excluded_tax_gateways')
                        <x-admin::form.error :message="$message"/>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <h3 class="card-title">Always Taxable Countries</h3>
                <p class="card-subtitle">
                    Select countries where users should never be exempt from tax, even when they provide company name and tax ID.
                </p>
                <div class="row g-2">
                    <div class="col">
                        <x-admin::form.select
                            wire:model="tax_non_exempt_countries"
                            id="tax_non_exempt_countries"
                            :options="World::countries()"
                            multiple
                        />
                    </div>
                    @error('tax_non_exempt_countries')
                        <x-admin::form.error :message="$message"/>
                    @enderror
                </div>
            </div>

    </x-admin::settings.page-form>
</div>
