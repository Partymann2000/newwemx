<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Subscription;

class SubscriptionsController extends Controller
{
    public function index()
    {
        return view('theme::dashboard.subscriptions');
    }

    public function cancel(Subscription $subscription)
    {
        if ($subscription->user_id !== auth()->id()) {
            abort(403);
        }

        $cancelResponse = $subscription->cancelSubscription();

        // if cancel response is an array and contains redirect_url, redirect to that url
        if (is_array($cancelResponse) && isset($cancelResponse['redirect_url'])) {
            return redirect($cancelResponse['redirect_url']);
        }

        return redirect()->back();
    }
}
