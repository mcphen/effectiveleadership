<?php

namespace App\Cms;

use App\Models\SolutionCategory;
use Illuminate\Support\Str;

/**
 * Types de champs communs aux blocs et aux formulaires de l'admin :
 *
 *  text, textarea, richtext, number, range, toggle, select, color, date,
 *  image, file, link, icon, slug, repeater, blocks, seo
 *
 * Options d'un champ : name, type, label, t (traduisible), required, help, width (half|full),
 * default, options, options_source, fields (repeater), item_label, max, rows, side (colonne latérale).
 */
class Fields
{
    /** Remplace « options_source » par la liste d'options correspondante (récursif). */
    public static function resolveOptions(array $fields): array
    {
        return array_map(function (array $field) {
            if (isset($field['options_source'])) {
                $field['options'] = static::optionsFrom($field['options_source']);
            }

            if (! empty($field['fields'])) {
                $field['fields'] = static::resolveOptions($field['fields']);
            }

            return $field;
        }, $fields);
    }

    /** @return array<string|int, string> */
    public static function optionsFrom(string $source): array
    {
        return match ($source) {
            'solution_categories' => SolutionCategory::query()->with('parent')->orderBy('parent_id')->orderBy('sort')->get()
                ->sortBy(fn ($c) => sprintf('%05d-%05d-%05d', $c->parent?->sort ?? $c->sort, $c->parent ? 1 : 0, $c->sort))
                ->mapWithKeys(fn ($c) => [$c->id => $c->pathLabel()])->all(),
            'solution_poles' => SolutionCategory::query()->whereNull('parent_id')->orderBy('sort')->get()
                ->mapWithKeys(fn ($c) => [$c->id => $c->getTranslation('title', default_locale())])->all(),
            'post_types' => collect(config('elc.post_types'))->map(fn ($l) => $l['fr'])->all(),
            'roles' => config('elc.roles'),
            default => [],
        };
    }

    /** Valeur vide pour un champ (structure traduisible respectée). */
    public static function emptyValue(array $field): mixed
    {
        if (array_key_exists('default', $field)) {
            return $field['default'];
        }

        if (! empty($field['t'])) {
            return collect(locales())->map(fn () => '')->all();
        }

        return match ($field['type']) {
            'repeater', 'blocks' => [],
            'toggle' => false,
            'seo' => ['title' => ['fr' => '', 'en' => ''], 'description' => ['fr' => '', 'en' => ''], 'image' => null, 'noindex' => false],
            default => null,
        };
    }

    /**
     * Nettoie une valeur reçue de l'admin selon le schéma du champ
     * (supprime les clés inconnues, nettoie le HTML, normalise les types).
     */
    public static function sanitize(array $field, mixed $value): mixed
    {
        if (! empty($field['t'])) {
            $value = is_array($value) ? $value : [default_locale() => $value];

            return collect(locales())->mapWithKeys(function ($label, $locale) use ($field, $value) {
                return [$locale => static::sanitizeScalar($field, $value[$locale] ?? '')];
            })->all();
        }

        return match ($field['type']) {
            'repeater' => collect(is_array($value) ? $value : [])
                ->take($field['max'] ?? 100)
                ->map(fn ($item) => static::sanitizeItem($field['fields'] ?? [], is_array($item) ? $item : []))
                ->values()->all(),
            'blocks' => static::sanitizeBlocks(is_array($value) ? $value : []),
            'seo' => [
                'title' => static::sanitize(['type' => 'text', 't' => true], $value['title'] ?? []),
                'description' => static::sanitize(['type' => 'textarea', 't' => true], $value['description'] ?? []),
                'image' => static::sanitizeScalar(['type' => 'image'], $value['image'] ?? null),
                'noindex' => (bool) ($value['noindex'] ?? false),
            ],
            default => static::sanitizeScalar($field, $value),
        };
    }

    /** @param array<int, array> $fields */
    public static function sanitizeItem(array $fields, array $data): array
    {
        $clean = [];

        foreach ($fields as $field) {
            $clean[$field['name']] = static::sanitize($field, $data[$field['name']] ?? static::emptyValue($field));
        }

        // Identifiant stable des éléments de liste (utile au glisser-déposer).
        $clean['_id'] = isset($data['_id']) && is_string($data['_id']) ? Str::limit($data['_id'], 32, '') : Str::random(8);

        return $clean;
    }

    public static function sanitizeBlocks(array $blocks): array
    {
        $definitions = Blocks::all();
        $settingsFields = Blocks::settingsFields();

        return collect($blocks)
            ->filter(fn ($b) => is_array($b) && isset($b['type'], $definitions[$b['type']]))
            ->map(function (array $block) use ($definitions, $settingsFields) {
                $definition = $definitions[$block['type']];
                $data = static::sanitizeItem($definition['fields'], (array) ($block['data'] ?? []));
                unset($data['_id']);

                $settings = [];
                foreach ($settingsFields as $f) {
                    $settings[$f['name']] = static::sanitize($f, $block['settings'][$f['name']] ?? ($definition['settings'][$f['name']] ?? static::emptyValue($f)));
                }

                return [
                    'id' => is_string($block['id'] ?? null) ? Str::limit($block['id'], 32, '') : 'b_'.Str::random(8),
                    'type' => $block['type'],
                    'hidden' => (bool) ($block['hidden'] ?? false),
                    'data' => $data,
                    'settings' => $settings,
                ];
            })
            ->values()->all();
    }

    protected static function sanitizeScalar(array $field, mixed $value): mixed
    {
        return match ($field['type']) {
            'toggle' => (bool) $value,
            'number', 'range' => is_numeric($value) ? $value + 0 : ($field['default'] ?? null),
            'richtext' => is_string($value) ? Html::clean($value) : '',
            'select' => match (true) {
                ! is_scalar($value) || $value === '' && ! array_key_exists('', $field['options'] ?? []) => $field['default'] ?? null,
                isset($field['options_source']) => (string) $value,
                array_key_exists((string) $value, $field['options'] ?? []) => (string) $value,
                default => $field['default'] ?? null,
            },
            'color' => is_string($value) && preg_match('/^#[0-9a-fA-F]{3,8}$/', $value) ? $value : ($field['default'] ?? null),
            'slug' => is_string($value) ? Str::slug($value) : '',
            default => is_scalar($value) ? trim(strip_tags((string) $value)) : null,
        };
    }

    /** Règles de validation Laravel générées depuis le schéma (champs de premier niveau). */
    public static function rules(array $fields): array
    {
        $rules = [];

        foreach ($fields as $field) {
            $name = $field['name'];
            $required = ! empty($field['required']);

            if (! empty($field['t'])) {
                $rules[$name] = ['nullable', 'array'];
                $rules["$name.".default_locale()] = [$required ? 'required' : 'nullable', 'string', $field['type'] === 'richtext' ? 'max:200000' : 'max:'.($field['max_length'] ?? 5000)];
                foreach (array_keys(locales()) as $locale) {
                    if ($locale !== default_locale()) {
                        $rules["$name.$locale"] = ['nullable', 'string', $field['type'] === 'richtext' ? 'max:200000' : 'max:'.($field['max_length'] ?? 5000)];
                    }
                }

                continue;
            }

            $rules[$name] = match ($field['type']) {
                'repeater', 'blocks', 'seo' => ['nullable', 'array'],
                'toggle' => ['nullable', 'boolean'],
                'number', 'range' => [$required ? 'required' : 'nullable', 'numeric'],
                'date' => [$required ? 'required' : 'nullable', 'date'],
                'email' => [$required ? 'required' : 'nullable', 'email', 'max:255'],
                'select' => [$required ? 'required' : 'nullable'],
                default => [$required ? 'required' : 'nullable', 'string', 'max:'.($field['max_length'] ?? 5000)],
            };
        }

        return $rules;
    }

    /** Libellés lisibles des erreurs (« title.fr » → « Titre (FR) »). */
    public static function attributes(array $fields): array
    {
        $attributes = [];

        foreach ($fields as $field) {
            $attributes[$field['name']] = $field['label'];
            foreach (array_keys(locales()) as $locale) {
                $attributes["{$field['name']}.$locale"] = $field['label'].' ('.strtoupper($locale).')';
            }
        }

        return $attributes;
    }
}
