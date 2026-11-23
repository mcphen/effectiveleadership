<?php

namespace App\Cms;

use App\Cms\SettingsSchema;

/** Transforme les réglages « Couleurs & typographie » en variables CSS pour le site public. */
class Theme
{
    public static function cssVariables(): string
    {
        $vars = [
            '--brand' => static::rgb(setting('theme.primary'), '#156080'),
            '--brand2' => static::rgb(setting('theme.secondary'), '#008080'),
            '--ink' => static::rgb(setting('theme.dark'), '#0C2B3A'),
            '--accent2' => static::rgb(setting('theme.accent'), '#C9973F'),
            '--radius' => ((int) (setting('theme.radius') ?? 4)).'px',
            '--font-heading' => "'".static::font('heading')."'",
            '--font-body' => "'".static::font('body')."'",
        ];

        return collect($vars)->map(fn ($v, $k) => "$k: $v;")->implode(' ');
    }

    public static function fontsUrl(): string
    {
        $families = collect([static::font('heading'), static::font('body')])->unique()
            ->map(fn ($f) => 'family='.str_replace(' ', '+', $f).':ital,wght@0,400;0,600;0,700;1,400')
            ->implode('&');

        return "https://fonts.googleapis.com/css2?$families&display=swap";
    }

    protected static function font(string $which): string
    {
        $allowed = $which === 'heading' ? SettingsSchema::HEADING_FONTS : SettingsSchema::BODY_FONTS;
        $value = setting("theme.{$which}_font");

        return array_key_exists((string) $value, $allowed) ? $value : array_key_first($allowed);
    }

    /** « #156080 » → « 21 96 128 » (format attendu par Tailwind pour l'opacité). */
    protected static function rgb(?string $hex, string $default): string
    {
        $hex = ltrim(preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', (string) $hex) ? $hex : $default, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return implode(' ', array_map('hexdec', str_split($hex, 2)));
    }
}
