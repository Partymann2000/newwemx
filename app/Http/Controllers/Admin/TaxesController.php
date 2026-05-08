<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesTaxCountry;

class TaxesController extends Controller
{
    public function index()
    {
        return view('admin::taxes.index');
    }

    public function create()
    {
        return view('admin::taxes.create');
    }

    public function edit($countryCode)
    {
        $country = SalesTaxCountry::where('country_code', $countryCode)->firstOrFail();
        return view('admin::taxes.edit', compact('country'));
    }
}
