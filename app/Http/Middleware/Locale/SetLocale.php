<?php

namespace App\Http\Middleware\Locale;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * @var array<string>
     */
    protected array $supportedLocales;

    public function __construct()
    {
        $this->supportedLocales = config('app.supported_locales', ['en']);
    }

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->determineLocale($request);

        App::setLocale($locale);

        $localeCookieKey = config('app.locale_cookie', 'locale');
        if ($request->cookie($localeCookieKey) !== $locale) {
            Cookie::queue($localeCookieKey, $locale, 60 * 24 * 30); // 30 days
        }

        return $next($request);
    }

    protected function determineLocale(Request $request): string
    {
        $cookie = $request->cookie(config('app.locale_cookie', 'locale'));
        if (is_string($cookie) && $this->isSupported($cookie)) {
            return $cookie;
        }

        if ($browser = $this->parseAcceptLanguage($request)) {
            return $browser;
        }

        return config('app.locale', 'en');
    }

    protected function isSupported(string $locale): bool
    {
        return in_array($locale, $this->supportedLocales, true);
    }

    protected function parseAcceptLanguage(Request $request): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');
        if (empty($acceptLanguage)) {
            return null;
        }

        // Parse the header: en-US,en;q=0.9,fr;q=0.8
        $acceptedLocales = [];
        foreach (explode(',', $acceptLanguage) as $part) {
            $item = explode(';q=', $part);
            $locale = trim($item[0]);
            $weight = isset($item[1]) ? (float) $item[1] : 1.0;
            $acceptedLocales[] = compact('locale', 'weight');
        }

        // Sort by weight (highest first)
        usort($acceptedLocales, fn ($a, $b) => $b['weight'] <=> $a['weight']);

        foreach ($acceptedLocales as $item) {
            $primary = explode('-', $item['locale'])[0];
            if ($this->isSupported($primary)) {
                return $primary;
            }

            if ($this->isSupported($item['locale'])) {
                return $item['locale'];
            }
        }

        return null;
    }
}
