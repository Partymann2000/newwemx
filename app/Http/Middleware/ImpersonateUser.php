<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;

class ImpersonateUser
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $impersonating = session('impersonate');

        if ($impersonating) {
            $user = User::find($impersonating);

            // If user not found or user is staff, stop impersonation
            if (!$user OR $user->isStaff()) {
                session()->forget('impersonate');
                return $next($request);
            }

            auth()->onceUsingId(session('impersonate'));
        }

        return $next($request);
    }
}
