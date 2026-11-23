<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Le site est servi derrière le pare-feu Sucuri (GoDaddy Website Security) :
        // on lit la vraie IP du visiteur et le protocole dans les en-têtes X-Forwarded-*.
        $middleware->trustProxies(at: env('TRUSTED_PROXIES', '*'));

        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'active' => \App\Http\Middleware\EnsureActiveUser::class,
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'locale' => \App\Http\Middleware\SetLocale::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
