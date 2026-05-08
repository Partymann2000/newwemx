<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireTFAMiddleware
{
    public array $ignoreRoutes = [
        'default.livewire.update',
        'livewire.update',
        'require-2fa',
        'lost-access-2fa',
        'logout',
    ];

    public function handle(Request $request, Closure $next)
    {
        $authUser = auth()->user();
        if ($authUser && $authUser->tfa_enabled) {
            // if route is in ignoreRoutes, skip the check
            if (!in_array($request->route()->getName(), $this->ignoreRoutes)) {
                // check if session has tfa_passed_at and if it's older than 2 hours
                $tfaPassedAt = session('tfa_passed_at');

                if (!$tfaPassedAt || now()->diffInMinutes($tfaPassedAt) > 120) {
                    return redirect()->route('require-2fa');
                }
            }
        }

        return $next($request);
    }
}
