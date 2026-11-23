<?php

use App\Models\Setting;
use Illuminate\Support\Str;

if (! function_exists('locales')) {
    /** @return array<string, string> code => libellé */
    function locales(): array
    {
        return config('elc.locales');
    }
}

if (! function_exists('default_locale')) {
    function default_locale(): string
    {
        return array_key_first(config('elc.locales'));
    }
}

if (! function_exists('tr')) {
    /**
     * Lit une valeur traduisible {"fr": "...", "en": "..."} dans la langue courante,
     * avec repli sur la langue par défaut. Une chaîne simple est renvoyée telle quelle.
     */
    function tr(mixed $value, ?string $locale = null): string
    {
        if (is_null($value)) {
            return '';
        }

        if (! is_array($value)) {
            return (string) $value;
        }

        $locale ??= app()->getLocale();

        $text = $value[$locale] ?? null;

        if ($text === null || $text === '') {
            $text = $value[default_locale()] ?? '';
        }

        return is_string($text) ? $text : '';
    }
}

if (! function_exists('media_url')) {
    /** URL publique d'un fichier de la médiathèque (chemin relatif au disque public). */
    function media_url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '//', '/', 'data:'])) {
            return $path;
        }

        return asset('storage/'.$path);
    }
}

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('segment')) {
    /** Préfixe d'URL traduit, ex. segment('case_studies') → « realisations ». */
    function segment(string $key, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return config("elc.segments.$key.$locale") ?? config("elc.segments.$key.".default_locale());
    }
}

if (! function_exists('localized_url')) {
    /** Construit /{locale}/{path} . */
    function localized_url(string $path = '', ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return url(trim($locale.'/'.ltrim($path, '/'), '/'));
    }
}

if (! function_exists('resolve_link')) {
    /**
     * Résout un lien saisi dans l'admin. Un lien peut être :
     * - une chaîne libre (URL externe, #ancre, /chemin, mailto:, tel:)
     * - un lien interne « page:12 », « solution:4 », « category:2 », « case:3 », « post:5 », « job:1 »
     */
    function resolve_link(mixed $link, ?string $locale = null): string
    {
        $link = is_array($link) ? tr($link, $locale) : (string) $link;
        $link = trim($link);

        if ($link === '') {
            return '#';
        }

        if (preg_match('/^(page|solution|category|case|post|job):(\d+)$/', $link, $m)) {
            $model = match ($m[1]) {
                'page' => \App\Models\Page::find($m[2]),
                'solution' => \App\Models\Solution::find($m[2]),
                'category' => \App\Models\SolutionCategory::find($m[2]),
                'case' => \App\Models\CaseStudy::find($m[2]),
                'post' => \App\Models\Post::find($m[2]),
                'job' => \App\Models\JobOffer::find($m[2]),
            };

            return $model?->url($locale) ?? '#';
        }

        if (Str::startsWith($link, '/') && ! Str::startsWith($link, '//')) {
            return url($link);
        }

        return $link;
    }
}

if (! function_exists('is_external_link')) {
    function is_external_link(string $url): bool
    {
        return Str::startsWith($url, ['http://', 'https://']) && ! Str::startsWith($url, url('/'));
    }
}
