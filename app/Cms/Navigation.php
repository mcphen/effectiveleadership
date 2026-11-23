<?php

namespace App\Cms;

use App\Models\Menu;
use Illuminate\Support\Str;

/** Prépare les menus (libellés, URL, lien actif) dans la langue courante. */
class Navigation
{
    /** @return array<int, array{label: string, url: string, new_tab: bool, active: bool, description: string, children: array}> */
    public static function items(string $location): array
    {
        return static::resolve(Menu::at($location));
    }

    protected static function resolve(array $items): array
    {
        $current = rtrim(url()->current(), '/');

        return collect($items)->map(function ($item) use ($current) {
            $url = resolve_link($item['link'] ?? '');
            $label = tr($item['label'] ?? null) ?: static::targetTitle($item['link'] ?? '');
            $children = static::resolve($item['children'] ?? []);
            $normalized = rtrim($url, '/');
            $isHome = $normalized === rtrim(localized_url(), '/');

            $active = $url !== '#' && ($normalized === $current || (! $isHome && Str::startsWith($current, $normalized.'/')))
                || collect($children)->contains('active', true);

            return [
                'label' => $label ?: $url,
                'url' => $url,
                'new_tab' => (bool) ($item['new_tab'] ?? false),
                'active' => $active,
                'description' => tr($item['description'] ?? null),
                'children' => $children,
            ];
        })->filter(fn ($i) => $i['label'] !== '')->values()->all();
    }

    /** Titre du contenu lié, quand aucun libellé n'est saisi. */
    protected static function targetTitle(string $link): string
    {
        if (! preg_match('/^(page|solution|category|case|post|job):(\d+)$/', $link, $m)) {
            return '';
        }

        $model = match ($m[1]) {
            'page' => \App\Models\Page::find($m[2]),
            'solution' => \App\Models\Solution::find($m[2]),
            'category' => \App\Models\SolutionCategory::find($m[2]),
            'case' => \App\Models\CaseStudy::find($m[2]),
            'post' => \App\Models\Post::find($m[2]),
            'job' => \App\Models\JobOffer::find($m[2]),
        };

        return (string) ($model?->title ?? '');
    }
}
