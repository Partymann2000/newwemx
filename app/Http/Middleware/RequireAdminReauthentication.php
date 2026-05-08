<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAdminReauthentication
{
    public array $ignoreRoutes = [
        'admin.reauthenticate',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $routeName = $request->route()?->getName();

        if (! $user || ! $user->isStaff()) {
            return $next($request);
        }

        if (in_array($routeName, $this->ignoreRoutes, true)) {
            return $next($request);
        }

        if (! session('admin_reauthenticated_at')) {
            session(['admin_reauth_redirect_to' => $request->fullUrl()]);

            return redirect()->route('admin.reauthenticate');
        }

        return $next($request);
    }
}
