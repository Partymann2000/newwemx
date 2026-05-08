<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Extension;

class CurrencyController extends Controller
{
    public function index()
    {
        return view('admin::currencies.index');
    }

    public function create()
    {
        return view('admin::currencies.create');
    }

    public function updateRates()
    {
        Currency::updateCurrencyRates();
        return redirect()->back()->with('success', 'Currency rates have been updated successfully.');
    }

    public function edit(Currency $currency)
    {
        return view('admin::currencies.edit', compact('currency'));
    }
}
