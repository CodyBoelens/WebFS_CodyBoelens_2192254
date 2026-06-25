<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * UC-13: Stel de applicatietaal in op basis van de sessie.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale', config('app.locale', 'nl'));

        if (in_array($locale, ['nl', 'en'])) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
