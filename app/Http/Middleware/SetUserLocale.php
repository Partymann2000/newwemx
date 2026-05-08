<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class SetUserLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (! config('app.installed', false)) {
            App::setLocale(config('app.locale', 'en'));

            return $next($request);
        }

        if (Auth::check()) {
            App::setLocale(Auth::user()->language);

            auth()->user()->lastSeenNow();
        } else {
            App::setLocale(session('locale', settings('language', 'en')));
        }

        return $next($request);
    }
}
