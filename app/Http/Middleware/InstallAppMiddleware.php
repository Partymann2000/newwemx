<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InstallAppMiddleware
{
    public array $ignoreRoutes = [
        'default.livewire.update',
        'livewire.update',
        'livewire.message',
        'install.index',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (! config('app.installed', false)) {
            $routeName = $request->route()?->getName();

            if ($routeName && in_array($routeName, $this->ignoreRoutes, true)) {
                return $next($request);
            }

            if ($request->is('livewire/*')) {
                return $next($request);
            }

            return redirect()->route('install.index');
        }

        return $next($request);
    }
}
