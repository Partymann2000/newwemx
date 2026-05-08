<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPendingOrSuspendedUser
{
    public $except = [
        'account-pending',
        'logout',
    ];

    public function handle(Request $request, Closure $next)
    {
        if(!auth()->check()) {
            return $next($request);
        }

        // if user is pending or suspended, redirect them to pending page
        if(in_array(auth()->user()->status, ['pending', 'suspended'])) {
            if ($request->route() && in_array($request->route()->getName(), $this->except)) {
                return $next($request);
            }

            return redirect()->route('account-pending');
        }

        return $next($request);
    }
}
