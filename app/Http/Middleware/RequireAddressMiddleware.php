<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireAddressMiddleware
{
    public array $ignoreRoutes = [
        'default.livewire.update',
        'livewire.update',
        'update-address',
        'logout',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && !auth()->user()->hasCompletedAddress() && settings('require_address', false)) {
            if (in_array($request->route()->getName(), $this->ignoreRoutes)) {
                return $next($request);
            }

            return redirect()->route('update-address');
        }

        return $next($request);
    }
}
