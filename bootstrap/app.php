<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware(['web', 'auth', 'admin', \App\Http\Middleware\RequireAdminReauthentication::class])
                ->prefix('admin')->name('admin.')
                ->group(base_path('routes/admin.php'));
            Route::middleware(['web'])->prefix('auth')
                ->group(base_path('routes/auth.php'));
        }
    )->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'gateways/webhooks/*',
            'gateways/callbacks/*',
        ]);

        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermissionMiddleware::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);

        $middleware->appendToGroup('web', [
            \App\Http\Middleware\SetUserLocale::class,
            \App\Http\Middleware\InstallAppMiddleware::class,
            \App\Http\Middleware\SyncRuntimeMiddleware::class,
            \App\Http\Middleware\CheckPendingOrSuspendedUser::class,
            \App\Http\Middleware\CheckActiveUserBan::class,
            \App\Http\Middleware\VerifyEmailMiddleware::class,
            \App\Http\Middleware\RequireAddressMiddleware::class,
            \App\Http\Middleware\RequireTFAMiddleware::class,
            \App\Http\Middleware\ImpersonateUser::class,
            \App\Http\Middleware\DefineCartMiddleware::class,
            \App\Http\Middleware\AdminPathMiddleware::class,
        ]);

        $middleware->trustProxies(at: '*');
    })->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
