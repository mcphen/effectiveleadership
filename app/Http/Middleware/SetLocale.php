<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

/** Applique la langue du préfixe d'URL /{locale}/… */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        if (! array_key_exists((string) $locale, locales())) {
            abort(404);
        }

        app()->setLocale($locale);
        Carbon::setLocale($locale);
        url()->defaults(['locale' => $locale]);

        // Le paramètre n'a pas à être transmis aux contrôleurs.
        $request->route()->forgetParameter('locale');

        return $next($request);
    }
}
