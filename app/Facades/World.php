<?php

namespace App\Facades;

use ResourceBundle;
use Illuminate\Support\Facades\Cache;

class World
{
    public static function countries($locale = 'en'): array
    {
        $countries = ResourceBundle::create($locale, 'ICUDATA-region')->get('Countries');

        $countryList = [];
        foreach ($countries as $key => $country) {
            if($key === 'ZZ' OR is_numeric($key)) {
                continue;
            }

            $countryList[$key] = $country;
        }

        return $countryList;
    }

    public static function countryName($countryCode)
    {
        $countries = self::countries();
        return $countries[$countryCode] ?? 'Unkown';
    }

    public static function states($countryCode): array
    {
        $countryCode = strtoupper($countryCode);

        if($countryCode == 'US') {
            return [
                'AL' => 'Alabama',
                'AK' => 'Alaska',
                'AZ' => 'Arizona',
                'AR' => 'Arkansas',
                'CA' => 'California',
                'CO' => 'Colorado',
                'CT' => 'Connecticut',
                'DE' => 'Delaware',
                'DC' => 'District Of Columbia',
                'FL' => 'Florida',
                'GA' => 'Georgia',
                'HI' => 'Hawaii',
                'ID' => 'Idaho',
                'IL' => 'Illinois',
                'IN' => 'Indiana',
                'IA' => 'Iowa',
                'KS' => 'Kansas',
                'KY' => 'Kentucky',
                'LA' => 'Louisiana',
                'ME' => 'Maine',
                'MD' => 'Maryland',
                'MA' => 'Massachusetts',
                'MI' => 'Michigan',
                'MN' => 'Minnesota',
                'MS' => 'Mississippi',
                'MO' => 'Missouri',
                'MT' => 'Montana',
                'NE' => 'Nebraska',
                'NV' => 'Nevada',
                'NH' => 'New Hampshire',
                'NJ' => 'New Jersey',
                'NM' => 'New Mexico',
                'NY' => 'New York',
                'NC' => 'North Carolina',
                'ND' => 'North Dakota',
                'OH' => 'Ohio',
                'OK' => 'Oklahoma',
                'OR' => 'Oregon',
                'PA' => 'Pennsylvania',
                'RI' => 'Rhode Island',
                'SC' => 'South Carolina',
                'SD' => 'South Dakota',
                'TN' => 'Tennessee',
                'TX' => 'Texas',
                'UT' => 'Utah',
                'VT' => 'Vermont',
                'VA' => 'Virginia',
                'WA' => 'Washington',
                'WV' => 'West Virginia',
                'WI' => 'Wisconsin',
                'WY' => 'Wyoming',
            ];
        }

        if($countryCode == 'CA') {
            return [
                'AB' => 'Alberta',
                'BC' => 'British Columbia',
                'MB' => 'Manitoba',
                'NB' => 'New Brunswick',
                'NL' => 'Newfoundland and Labrador',
                'NS' => 'Nova Scotia',
                'ON' => 'Ontario',
                'PE' => 'Prince Edward Island',
                'QC' => 'Quebec',
                'SK' => 'Saskatchewan',
                'NT' => 'Northwest Territories',
                'NU' => 'Nunavut',
                'YT' => 'Yukon',
            ];
        }

        return [];
    }

    public static function getCountry($countryCode, $locale = 'en'): string
    {
        $countries = self::countries($locale);
        return $countries[$countryCode] ?? '';
    }

    public static function languages($locale = 'en'): array
    {
        $languages = ResourceBundle::create($locale, 'ICUDATA-lang')->get('Languages');

        $languageList = [];
        foreach ($languages as $key => $language) {
            if($key === 'root' OR is_numeric($key)) {
                continue;
            }

            $languageList[$key] = $language;
        }

        return $languageList;
    }

    public static function timezones(): array
    {
        return \DateTimeZone::listIdentifiers();
    }

    public static function currencies($locale = 'en'): array
    {
        return [];
    }
}
