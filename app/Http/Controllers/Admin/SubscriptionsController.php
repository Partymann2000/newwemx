<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;

class SubscriptionsController extends Controller
{
    public function index()
    {
        return view('admin::subscriptions.index');
    }

    public function edit(Subscription $subscription)
    {
        return view('admin::subscriptions.edit', compact('subscription'));
    }
}
