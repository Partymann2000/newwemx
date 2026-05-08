<?php

namespace App\Actions;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Exception;

class SubscriptionActions extends Action
{
    public function cancelSubscriptionAsClient(array $input)
    {
        $validatedData = Validator::make($input, [
            'user_id' => ['required', 'exists:users,id'],
            'subscription_id' => ['required', 'exists:subscriptions,id'],
            'reason' => ['nullable', 'string', 'min:5', 'max:75'],
        ])->validate();

        $subscription = Subscription::find($validatedData['subscription_id']);

        if ($subscription->user_id != $validatedData['user_id']) {
            throw ValidationException::withMessages(['subscription_id' => 'The subscription does not belong to the specified user.']);
        }

        if ($subscription->status !== 'active') {
            throw ValidationException::withMessages(['subscription_id' => 'Only active subscriptions can be cancelled.']);
        }

        // if reason is not provided, set it to 'No reason provided'
        $subscription->cancelled($validatedData['reason'] ?? 'No reason provided');

        return $subscription;
    }

    public function cancelSubscriptionAsAdmin(array $input)
    {
        $validatedData = Validator::make($input, [
            'subscription_id' => ['required', 'exists:subscriptions,id'],
            'admin_id' => ['required', 'exists:users,id'],
        ])->validate();

        $subscription = Subscription::find($validatedData['subscription_id']);
        $adminUser = User::find($validatedData['admin_id']);

        $reason = 'Cancelled by admin: ' . $adminUser->username . ' (ID: ' . $adminUser->id . ')';

        if ($subscription->status !== 'active') {
            throw ValidationException::withMessages(['subscription_id' => 'Only active subscriptions can be cancelled.']);
        }

        // if reason is not provided, set it to 'No reason provided'
        $subscription->cancelled($reason);

        return $subscription;
    }
}
