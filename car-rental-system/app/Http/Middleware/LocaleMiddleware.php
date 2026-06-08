<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class LocaleMiddleware
{
    /**
     * Supported locales in the application.
     */
    private const SUPPORTED_LOCALES = ['en', 'fa', 'ps'];

    /**
     * Handle an incoming request.
     * Sets the application locale based on:
     * 1. Authenticated user's locale field
     * 2. Session locale (if previously set)
     * 3. Falls back to 'en'
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        App::setLocale($locale);
        session(['locale' => $locale]);

        return $next($request);
    }

    /**
     * Resolve the locale to use.
     */
    private function resolveLocale(Request $request): string
    {
        // 1. Check authenticated user's locale preference
        if (auth()->check()) {
            $userLocale = auth()->user()->locale;

            if ($userLocale && in_array($userLocale, self::SUPPORTED_LOCALES)) {
                return $userLocale;
            }
        }

        // 2. Check session for previously set locale
        $sessionLocale = session('locale');

        if ($sessionLocale && in_array($sessionLocale, self::SUPPORTED_LOCALES)) {
            return $sessionLocale;
        }

        // 3. Check URL query parameter ?lang=fa (useful for testing)
        $queryLocale = $request->query('lang');

        if ($queryLocale && in_array($queryLocale, self::SUPPORTED_LOCALES)) {
            return $queryLocale;
        }

        // 4. Fall back to default app locale
        return config('app.locale', 'en');
    }
}