<?php

namespace App\Actions;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\SalesTaxCountry;

class TaxActions extends Action
{
    /**
     * Create a new sales tax country as an admin.
     *
     * @throws ValidationException
     */
    public static function createSalesTaxCountryAsAdmin(array $input)
    {
        $validatedData = Validator::make($input, [
            'country_code' => ['required', 'string', 'size:2', 'unique:sales_tax_countries,country_code'],
            'sales_tax_rate' => ['required', 'numeric', 'min:0'],
            'sales_tax_name' => ['required', 'string', 'max:255'],
            'states' => ['sometimes', 'array'],
            'states.*.state_code' => ['sometimes', 'string', 'size:2'],
            'states.*.sales_tax_rate' => ['sometimes', 'numeric', 'min:0'],
            'states.*.sales_tax_name' => ['sometimes', 'string', 'max:255'],
        ])->validate();

        $country = SalesTaxCountry::create([
            'country_code' => $validatedData['country_code'],
            'sales_tax_rate' => $validatedData['sales_tax_rate'],
            'sales_tax_name' => $validatedData['sales_tax_name'],
        ]);

        if (isset($validatedData['states'])) {
            foreach ($validatedData['states'] as $stateData) {
                $country->states()->create([
                    'state_code' => $stateData['state_code'],
                    'sales_tax_rate' => $stateData['sales_tax_rate'],
                    'sales_tax_name' => $stateData['sales_tax_name'],
                ]);
            }
        }

        return $country;
    }

    /**
     * Update an existing sales tax country as an admin.
     *
     * @throws ValidationException
     */
    public static function updateSalesTaxCountryAsAdmin(array $input)
    {
        $validatedData = Validator::make($input, [
            'country_id' => ['required', 'exists:sales_tax_countries,id'],
            'sales_tax_rate' => ['required', 'numeric', 'min:0'],
            'sales_tax_name' => ['required', 'string', 'max:255'],
            'states' => ['sometimes', 'array'],
            'states.*.state_code' => ['sometimes', 'string', 'size:2'],
            'states.*.sales_tax_rate' => ['sometimes', 'numeric', 'min:0'],
            'states.*.sales_tax_name' => ['sometimes', 'string', 'max:255'],
        ])->validate();

        $country = SalesTaxCountry::where('id', $validatedData['country_id'])->firstOrFail();

        $country->update([
            'sales_tax_rate' => $validatedData['sales_tax_rate'],
            'sales_tax_name' => $validatedData['sales_tax_name'],
        ]);

        if (isset($validatedData['states'])) {
            // delete all current states
            $country->states()->delete();

            // replace with new states
            foreach ($validatedData['states'] as $stateData) {
                $country->states()->create([
                    'state_code' => $stateData['state_code'],
                    'sales_tax_rate' => $stateData['sales_tax_rate'],
                    'sales_tax_name' => $stateData['sales_tax_name'],
                ]);
            }
        }

        return $country;
    }
}
