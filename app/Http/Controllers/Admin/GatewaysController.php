<?php

namespace App\Http\Controllers\Admin;

use App\Models\GatewayConfig;

class GatewaysController
{
    public function index()
    {
        return view('admin::gateways.index');
    }

    public function configs()
    {
        return view('admin::gateways.configs');
    }

    public function create()
    {
        return view('admin::gateways.create');
    }

    public function edit(GatewayConfig $gateway)
    {
        return view('admin::gateways.edit', ['gateway' => $gateway]);
    }
}
