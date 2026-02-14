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
        // Force unauthenticated users to login page (no direct dashboard access)
        $middleware->redirectGuestsTo(fn ($request) => route('login'));
        // Logged-in users visiting login page go to dashboard
        $middleware->redirectUsersTo(fn ($request) => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => __('Session expired. Please try again.')], 419);
            }
            // If session expired on logout, send to login instead of back (avoids 419 page)
            if ($request->is('logout')) {
                return redirect()->route('login')
                    ->with('error', __('Session expired. Please try again.'));
            }
            return redirect()->back()
                ->withInput($request->except('password', '_token'))
                ->with('error', __('Session expired. Please try again.'));
        });
    })->create();
