<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Address;

class DashboardController extends Controller
{
    public function index()
    {
        $countryUsers = Address::selectRaw('country, COUNT(*) as total')
            ->groupBy('country')
            ->pluck('total', 'country')
            ->toArray();

        // remove empty country codes
        unset($countryUsers['']);

        // sort by total descending
        arsort($countryUsers);

        return view('admin::dashboard.index', compact('countryUsers'));
    }

    public function toggleLocale($locale)
    {
        // ensure the language is in the list of available languages
        if (!array_key_exists($locale, config('languages.languages'))) {
            return redirect()->back();
        }

        auth()->user()->update(['language' => $locale]);

        return redirect()->back();
    }
}
