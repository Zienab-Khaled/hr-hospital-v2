<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        $middleware->redirectGuestsTo(fn ($request) => route('login'));
        $middleware->redirectUsersTo(fn ($request) => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => __('Session expired. Please try again.')], 419);
            }
            if ($request->is('logout')) {
                return redirect()->route('login')
                    ->with('error', __('Session expired. Please try again.'));
            }
            return redirect()->back()
                ->withInput($request->except('password', '_token'))
                ->with('error', __('Session expired. Please try again.'));
        });

        // عند خطأ صلاحيات مفتاح OAuth (بعد فترة عدم استخدام أو على السيرفر): إعادة توجيه لتسجيل الدخول بدل 500
        $exceptions->renderable(function (\Throwable $e, $request) {
            $msg = $e->getMessage();
            $isOauthKeyError = (str_contains($msg, 'oauth-public.key') || str_contains($msg, 'oauth-private.key'))
                && (str_contains($msg, 'permission') || str_contains($msg, '644'));
            if ($isOauthKeyError) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => __('Session or server error. Please log in again.')], 500);
                }
                return redirect()->route('login')
                    ->with('error', __('Session or server error. Please log in again.'));
            }
            return null;
        });
    })->create();
