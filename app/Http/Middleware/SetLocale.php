<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->get('lang')
            ?? session('locale')
            ?? $request->cookie('app_locale')
            ?? config('app.locale');

        if (in_array($locale, ['ar', 'en'], true)) {
            session(['locale' => $locale]);
            App::setLocale($locale);
            Cookie::queue('app_locale', $locale, 60 * 24 * 365, '/', null, false, false);
        }

        return $next($request);
    }
}
