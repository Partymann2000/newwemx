<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyEmailMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if(!auth()->check()) {
            return $next($request);
        }

        if (!auth()->user()->hasVerifiedEmail()) {

            // if route is logout, continue
            if ($request->routeIs('logout')) {
                return $next($request);
            }

            return redirect()->route('verify-email');
        }

        return $next($request);
    }
}
