<?php

namespace App\Cms;

/** Icônes SVG du site (générées par scripts/build-icons.mjs à partir de Lucide). */
class Icons
{
    protected static ?array $icons = null;

    public static function svg(?string $name, string $class = 'size-5', float $stroke = 1.75): string
    {
        static::$icons ??= json_decode(file_get_contents(resource_path('data/icons.json')), true)['icons'] ?? [];

        if (! $name || ! isset(static::$icons[$name])) {
            return '';
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="'.$stroke.'" '
            .'stroke-linecap="round" stroke-linejoin="round" class="'.e($class).'" aria-hidden="true">'.static::$icons[$name].'</svg>';
    }
}
