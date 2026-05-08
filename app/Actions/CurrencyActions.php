<?php

namespace App\Actions;

use App\Actions\Action;
use App\Models\Currency;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CurrencyActions extends Action
{
    /**
     * Create a new category
     *
     * @throws ValidationException
     */
    public static function createCurrenyAsAdmin(array $input)
    {
        $marketRate = self::getRateFor($input['currency'] ?? 'null');
        if(!$marketRate) {
            throw ValidationException::withMessages([
                'currency' => 'The currency code is invalid or not supported.',
            ]);
        }

        $validatedData = Validator::make($input, [
            'currency' => ['required', 'string', 'size:3', 'alpha','unique:currencies,currency'],
            'display_name' => ['required', 'string', 'max:255'],
            'manual_rate' => ['required_if:use_manual_rate,true', 'numeric'],
            'is_active' => ['sometimes', 'boolean'],
            'use_manual_rate' => ['required', 'boolean'],
        ])->validate();

        $validatedData['market_rate'] = $marketRate;

        return Currency::create(self::omitNullValues($validatedData));
    }

    public static function getRateFor($currencyCode)
    {
        $rates = Currency::getRatesFromApi();

        if(isset($rates['rates'][$currencyCode])) {
            return round($rates['rates'][$currencyCode], 2);
        }

        return null;
    }

    public static function updateCurrencyAsAdmin(array $input)
    {
        $marketRate = self::getRateFor($input['currency'] ?? 'null');

        if(!$marketRate) {
            throw ValidationException::withMessages([
                'currency' => 'The currency code is invalid or not supported.',
            ]);
        }

        $validatedData = Validator::make($input, [
            'currency' => ['required', 'string', 'size:3', 'alpha', 'exists:currencies,currency'],
            'display_name' => ['sometimes', 'required', 'string', 'max:255'],
            'manual_rate' => ['required_if:use_manual_rate,true', 'numeric'],
            'is_active' => ['sometimes', 'boolean'],
            'use_manual_rate' => ['sometimes', 'required', 'boolean'],
        ])->validate();

        $currency = Currency::find($validatedData['currency']);

        $validatedData['market_rate'] = $marketRate;

        $currency->update(self::omitNullValues($validatedData));

        return $currency;
    }
}
