<?php

namespace App\Cms\Resources;

use App\Cms\Fields;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Décrit un type de contenu géré par l'admin générique
 * (liste, création, édition, suppression, réordonnancement).
 */
abstract class Resource
{
    /** @var class-string<Model> */
    public static string $model;

    /** Segment d'URL de l'admin : /admin/content/{slug} */
    public static string $slug;

    public static string $label;

    public static string $singular;

    /** Féminin pour les libellés (« Nouvelle offre » / « Nouveau témoignage »). */
    public static bool $feminine = false;

    public static string $icon = 'file';

    /** Réordonnancement par glisser-déposer (colonne « sort »). */
    public static bool $sortable = true;

    /** Champ servant de titre dans la liste et de source au slug. */
    public static string $titleField = 'title';

    public static int $perPage = 25;

    /** @return array<int, array<string, mixed>> */
    abstract public static function fields(): array;

    /** Colonnes affichées dans la liste : [name, label, type: text|image|badge|bool|date] */
    public static function columns(): array
    {
        return [
            ['name' => static::$titleField, 'label' => 'Titre', 'type' => 'title'],
        ];
    }

    public static function query(): Builder
    {
        $query = static::$model::query();

        return static::$sortable ? $query->orderBy('sort')->orderBy('id') : $query->latest('id');
    }

    /** Colonnes JSON dans lesquelles chercher (FR). */
    public static function searchable(): array
    {
        return [static::$titleField];
    }

    /** Filtres de la liste : [name => [label, options]] */
    public static function filters(): array
    {
        return [];
    }

    public static function applyFilters(Builder $query, array $filters): Builder
    {
        return $query;
    }

    public static function newModel(): Model
    {
        return new static::$model;
    }

    /** Valeurs par défaut d'un nouvel élément. */
    public static function defaults(): array
    {
        return collect(static::fields())->mapWithKeys(fn ($f) => [$f['name'] => Fields::emptyValue($f)])->all();
    }

    /** Valeurs envoyées au formulaire Vue. */
    public static function toForm(Model $model): array
    {
        $values = ['id' => $model->getKey()];

        foreach (static::fields() as $field) {
            $name = $field['name'];

            if (! empty($field['t']) && method_exists($model, 'getTranslations')) {
                $translations = $model->getTranslations($name);
                $values[$name] = collect(locales())->mapWithKeys(fn ($l, $locale) => [$locale => $translations[$locale] ?? ''])->all();
            } else {
                $value = $model->getAttribute($name);
                $values[$name] = $value instanceof \DateTimeInterface
                    ? $value->format($field['type'] === 'datetime' ? 'Y-m-d\TH:i' : 'Y-m-d')
                    : ($value ?? Fields::emptyValue($field));
            }
        }

        return $values;
    }

    /** Applique les données validées au modèle. */
    public static function fill(Model $model, array $data): void
    {
        foreach (static::fields() as $field) {
            $name = $field['name'];

            // Le slug est toujours calculé (généré depuis le titre s'il n'est pas fourni).
            if ((! array_key_exists($name, $data) && $field['type'] !== 'slug') || ! empty($field['readonly'])) {
                continue;
            }

            $value = Fields::sanitize($field, $data[$name] ?? ($field['type'] === 'slug' && $model->exists ? $model->getTranslations($name) : []));

            if ($field['type'] === 'slug') {
                $value = static::uniqueSlugs($model, $value, $data[$field['from'] ?? static::$titleField] ?? []);
            }

            if (in_array($field['type'], ['date', 'datetime'], true) && ($value === '' || $value === null)) {
                $value = null;
            }

            if (! empty($field['t']) && method_exists($model, 'setTranslations')) {
                $model->replaceTranslations($name, array_filter($value, fn ($v) => $v !== '' && $v !== null));
            } else {
                $model->setAttribute($name, $value);
            }
        }
    }

    /**
     * Génère les slugs manquants depuis le titre et garantit leur unicité par langue.
     *
     * @return array<string, string>
     */
    protected static function uniqueSlugs(Model $model, array $slugs, mixed $titles): array
    {
        $titles = is_array($titles) ? $titles : [default_locale() => (string) $titles];

        foreach (array_keys(locales()) as $locale) {
            $slug = Str::slug($slugs[$locale] ?? '') ?: Str::slug($titles[$locale] ?? '');

            if ($slug === '') {
                $slugs[$locale] = '';

                continue;
            }

            $base = $slug;
            $i = 2;
            while (static::$model::query()
                ->where("slug->$locale", $slug)
                ->when($model->exists, fn ($q) => $q->whereKeyNot($model->getKey()))
                ->when(static::slugScope($model), fn ($q, $scope) => $scope($q))
                ->exists()) {
                $slug = $base.'-'.$i++;
            }

            $slugs[$locale] = $slug;
        }

        return $slugs;
    }

    /** Restreint le contrôle d'unicité du slug (ex. offres d'une même catégorie). */
    protected static function slugScope(Model $model): ?\Closure
    {
        return null;
    }

    /** Ligne de liste. */
    public static function toRow(Model $model): array
    {
        $row = ['id' => $model->getKey(), 'url' => method_exists($model, 'url') ? $model->url(default_locale()) : null];

        foreach (static::columns() as $column) {
            $name = $column['name'];
            $value = $column['value'] ?? null;

            if ($value instanceof \Closure) {
                $row[$name] = $value($model);

                continue;
            }

            $raw = $model->getAttribute($name);

            if (method_exists($model, 'isTranslatableAttribute') && $model->isTranslatableAttribute($name)) {
                $raw = $model->getTranslation($name, default_locale(), true);
            }

            $row[$name] = $raw instanceof \DateTimeInterface ? $raw->format('d/m/Y') : $raw;
        }

        // Indique les traductions anglaises manquantes.
        if (method_exists($model, 'isTranslatableAttribute') && $model->isTranslatableAttribute(static::$titleField)) {
            $row['_missing_en'] = blank($model->getTranslations(static::$titleField)['en'] ?? null);
        }

        return $row;
    }

    /** Champ « publié » commun. */
    protected static function publishedField(string $label = 'Publié sur le site'): array
    {
        return ['name' => 'is_published', 'type' => 'toggle', 'label' => $label, 'default' => true, 'side' => true];
    }

    protected static function seoField(): array
    {
        return ['name' => 'seo', 'type' => 'seo', 'label' => 'Référencement (SEO)', 'side' => true];
    }

    protected static function slugField(string $help = 'Partie de l\'adresse de la page. Générée automatiquement depuis le titre si vide.'): array
    {
        return ['name' => 'slug', 'type' => 'slug', 'label' => 'Adresse (slug)', 't' => true, 'from' => static::$titleField, 'help' => $help, 'side' => true];
    }

    public static function meta(): array
    {
        return [
            'slug' => static::$slug,
            'label' => static::$label,
            'singular' => static::$singular,
            'feminine' => static::$feminine,
            'icon' => static::$icon,
            'sortable' => static::$sortable,
            'titleField' => static::$titleField,
        ];
    }
}
