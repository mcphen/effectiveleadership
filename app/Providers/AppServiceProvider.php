<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Spatie\Translatable\Translatable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Texte non traduit : on affiche la langue principale du site (config/elc.php),
        // quel que soit APP_FALLBACK_LOCALE dans le .env, puis n'importe quelle langue disponible.
        $this->app->make(Translatable::class)->fallback(fallbackLocale: default_locale(), fallbackAny: true);

        // En production, toutes les URL générées sont en https.
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }
    }
}
