<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED_LOCALES = ['fr', 'de', 'en'];
    private const DEFAULT_LOCALE    = 'fr';

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->query('lang')
            ?? $this->parseAcceptLanguageHeader($request->header('Accept-Language', ''))
            ?? self::DEFAULT_LOCALE;

        // Prefer authenticated user's preferred language
        if ($request->user() && property_exists($request->user(), 'preferred_language')) {
            $locale = $request->user()->preferred_language ?? $locale;
        }

        $locale = strtolower(substr($locale, 0, 2));

        if (! in_array($locale, self::SUPPORTED_LOCALES)) {
            $locale = self::DEFAULT_LOCALE;
        }

        app()->setLocale($locale);

        return $next($request);
    }

    private function parseAcceptLanguageHeader(string $header): ?string
    {
        if (empty($header)) {
            return null;
        }

        foreach (explode(',', $header) as $part) {
            $code = strtolower(substr(trim(explode(';', $part)[0]), 0, 2));
            if (in_array($code, self::SUPPORTED_LOCALES)) {
                return $code;
            }
        }

        return null;
    }
}
