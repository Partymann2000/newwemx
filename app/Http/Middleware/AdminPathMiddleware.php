<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminPathMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // If the path contains 'admin', check for admin privileges
        if (str_contains($request->path(), 'admin')) {
            if (!auth()->check() || !auth()->user()->isStaff()) {
                abort(403);
            }
        }

        return $next($request);
    }
}
