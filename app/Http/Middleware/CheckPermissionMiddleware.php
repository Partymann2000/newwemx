<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param  Closure  $next
     * @param string|null $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $permission = null): mixed
    {
        if (!auth()->check() OR !auth()->user()->isStaff()) {
            abort(403);
        }

        if (!$permission AND !auth()->user()->isAdmin()) {
            abort(403);
        }

        if(!auth()->user()->hasPermission($permission)) {
            abort(403);
        }

        return $next($request);
    }
}
