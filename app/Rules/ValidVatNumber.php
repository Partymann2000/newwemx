<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use SoapClient;
use SoapFault;

class ValidVatNumber implements Rule
{
    protected ?string $countryCode;

    protected string $error = '';

    // List of VIES-valid EU country codes
    protected array $viesCountries = [
        'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR',
        'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL',
        'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE',
    ];

    public function __construct(?string $countryCode = null)
    {
        $this->countryCode = $countryCode ? strtoupper($countryCode) : null;
    }

    public function passes($attribute, $value)
    {
        $vat = strtoupper(preg_replace('/\s+/', '', $value)); // sanitize input

        // If country code is missing or not in VIES, skip validation
        if ($this->countryCode === null || ! in_array($this->countryCode, $this->viesCountries)) {
            return true;
        }

        // Extract country code from VAT if present
        if (preg_match('/^([A-Z]{2})([A-Z0-9]+)$/', $vat, $matches)) {
            $vatCountry = $matches[1];
            $vatNumber = $matches[2];
        } else {
            $vatCountry = $this->countryCode;
            $vatNumber = $vat;
        }

        try {
            $client = new SoapClient('https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl');

            $response = $client->checkVat([
                'countryCode' => $vatCountry,
                'vatNumber' => $vatNumber,
            ]);

            return $response->valid;
        } catch (SoapFault $e) {
            $this->error = 'VIES service is currently unavailable.';

            return false;
        }
    }

    public function message()
    {
        return $this->error ?: 'The VAT number is invalid for cross-border trade within the EU.';
    }
}
