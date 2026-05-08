<?php

namespace App\Facades;

use App\Models\GatewayConfig;
use App\Models\SalesTaxCountry;
use App\Rules\ValidVatNumber;

class Tax
{
    public static function calculateSalesTax(
        $amount,
        $countryCode,
        $stateCode = null,
        $taxId = null,
        $gatewayConfigId = null
    ): array {
        if (! settings('enable_sales_tax', false)) {
            // If sales tax is not enabled, return the amount without tax
            return [
                'amount_before_tax' => $amount,
                'amount_after_tax' => $amount,
                'tax_amount' => 0,
                'tax_rate' => 0,
                'tax_name' => 'Sales Tax',
            ];
        }

        $excludedGatewayIds = array_filter(explode(',', (string) settings('excluded_tax_gateways', '')));

        // Keep balance gateway excluded from tax by default when no explicit setting is stored yet.
        if (empty($excludedGatewayIds)) {
            $excludedGatewayIds = GatewayConfig::query()
                ->where('extension_identifier', 'gateway-balance')
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->toArray();
        }

        if ($gatewayConfigId && in_array((string) $gatewayConfigId, $excludedGatewayIds, true)) {
            return [
                'amount_before_tax' => $amount,
                'amount_after_tax' => $amount,
                'tax_amount' => 0,
                'tax_rate' => 0,
                'tax_name' => 'Sales Tax',
            ];
        }

        $country = SalesTaxCountry::where('country_code', $countryCode)
            ->where('is_active', true)
            ->first();

        if (! $country) {
            return [
                'amount_before_tax' => $amount,
                'amount_after_tax' => $amount,
                'tax_amount' => 0,
                'tax_rate' => 0,
                'tax_name' => 'Sales Tax',
            ]; // No tax for unknown countries
        }

        $rate = $country->sales_tax_rate;
        $taxName = $country->sales_tax_name;

        // For jurisdictions with state-level tax, require a matching active state.
        if (in_array($countryCode, ['US', 'CA'])) {
            $state = $country->states()
                ->where('state_code', $stateCode)
                ->where('is_active', true)
                ->first();

            if (! $state) {
                return [
                    'amount_before_tax' => $amount,
                    'amount_after_tax' => $amount,
                    'tax_amount' => 0,
                    'tax_rate' => 0,
                    'tax_name' => $taxName ?: 'Sales Tax',
                ];
            }

            $rate = $state->sales_tax_rate;
            $taxName = $state->sales_tax_name;
        }

        $taxIdProvided = is_string($taxId) && trim($taxId) !== '';
        $nonExemptCountries = array_filter(explode(',', (string) settings('tax_non_exempt_countries', '')));

        if (
            settings('enable_exempt_sales_tax', true)
            && $taxIdProvided
            && ! in_array($countryCode, $nonExemptCountries, true)
        ) {
            $isValidForExemption = true;

            if (settings('verify_vat_id', false)) {
                $isValidForExemption = (new ValidVatNumber($countryCode))->passes('tax_id', $taxId);
            }

            if ($isValidForExemption) {
                return [
                    'amount_before_tax' => $amount,
                    'amount_after_tax' => $amount,
                    'tax_amount' => 0,
                    'tax_rate' => 0,
                    'tax_name' => $taxName ?: 'Sales Tax',
                ];
            }
        }

        // if rate is 0, return no tax
        if ($rate <= 0) {
            return [
                'amount_before_tax' => $amount,
                'amount_after_tax' => $amount,
                'tax_amount' => 0,
                'tax_rate' => 0,
                'tax_name' => $taxName,
            ];
        }

        if (settings('include_tax_in_price', false)) {
            // If tax is included in the price, we need to calculate the amount before tax
            $taxAmount = round($amount * ($rate / (100 + $rate)), 2);
            $amountAfterTax = $amount;
            $amountBeforeTax = round($amount - $taxAmount, 2);
        } else {
            $taxAmount = round($amount * ($rate / 100), 2);
            $amountAfterTax = round($amount + $taxAmount, 2);
            $amountBeforeTax = $amount;
        }

        return [
            'amount_before_tax' => $amountBeforeTax,
            'amount_after_tax' => $amountAfterTax,
            'tax_amount' => $taxAmount,
            'tax_rate' => $rate,
            'tax_name' => $taxName,
        ];
    }
}
