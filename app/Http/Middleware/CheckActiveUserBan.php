<?php

namespace App\Http\Middleware;

use App\Models\Session;
use Closure;
use Illuminate\Http\Request;

class CheckActiveUserBan
{
    public array $except = [
        'account-suspended',
        'logout',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return $next($request);
        }

        if ($request->route() && in_array($request->route()->getName(), $this->except, true)) {
            return $next($request);
        }

        $user = auth()->user();

        $activeBan = $user->activeBan();

        if (!$activeBan) {
            $sessionIp = Session::query()
                ->where('user_id', $user->id)
                ->whereNotNull('ip_address')
                ->latest('last_activity')
                ->value('ip_address');

            if ($sessionIp) {
                $activeBan = \App\Models\UserBan::query()
                    ->where('is_ip_ban', true)
                    ->where('ip_address', $sessionIp)
                    ->whereNull('lifted_at')
                    ->where(function ($query) {
                        $query->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    })
                    ->latest()
                    ->first();
            }
        }

        if ($activeBan) {
            return redirect()->route('account-suspended');
        }

        return $next($request);
    }
}
