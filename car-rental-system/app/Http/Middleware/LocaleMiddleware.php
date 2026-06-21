<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class LocaleMiddleware
{
    private const SUPPORTED_LOCALES = ['en', 'fa', 'ps'];

    /**
     * Carbon locale map — maps app locale to Carbon/moment locale
     */
    private const CARBON_LOCALES = [
        'en' => 'en',
        'fa' => 'fa',
        'ps' => 'ps',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        // Set Laravel app locale
        App::setLocale($locale);
        session(['locale' => $locale]);

        // Set Carbon locale for translated dates
        $carbonLocale = self::CARBON_LOCALES[$locale] ?? 'en';
        Carbon::setLocale($carbonLocale);

        return $next($request);
    }

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

        // 3. Check URL query parameter ?lang=fa
        $queryLocale = $request->query('lang');
        if ($queryLocale && in_array($queryLocale, self::SUPPORTED_LOCALES)) {
            return $queryLocale;
        }

        // 4. Fall back to default
        return config('app.locale', 'en');
    }
}