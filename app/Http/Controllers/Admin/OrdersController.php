<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrdersController extends Controller
{
    public function index()
    {
        return view('admin::orders.index');
    }

    public function create()
    {
        return view('admin::orders.create');
    }

    public function edit(Order $order)
    {
        return view('admin::orders.edit', compact('order'));
    }
}
