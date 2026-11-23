<?php

namespace App\Cms\Resources;

use App\Cms\Fields;
use App\Models\Solution;
use Illuminate\Database\Eloquent\Builder;

class SolutionResource extends Resource
{
    public static string $model = Solution::class;

    public static string $slug = 'solutions';

    public static string $label = 'Offres';

    public static string $singular = 'offre';

    public static bool $feminine = true;

    public static string $icon = 'briefcase';

    public static function fields(): array
    {
        return [
            ['name' => 'title', 'type' => 'text', 'label' => 'Nom de l\'offre', 't' => true, 'required' => true],
            ['name' => 'subtitle', 'type' => 'text', 'label' => 'Sous-titre', 't' => true],
            ['name' => 'summary', 'type' => 'textarea', 'label' => 'Résumé (affiché sur les cartes)', 't' => true, 'rows' => 3],
            ['name' => 'signature', 'type' => 'text', 'label' => 'Signature', 't' => true, 'help' => 'Phrase courte, ex. « Révéler. Aligner. Transformer. »'],
            ['name' => 'blocks', 'type' => 'blocks', 'label' => 'Contenu de la page'],
            ['name' => 'solution_category_id', 'type' => 'select', 'label' => 'Catégorie', 'options_source' => 'solution_categories', 'required' => true, 'side' => true],
            static::publishedField(),
            ['name' => 'is_featured', 'type' => 'toggle', 'label' => 'Mettre en avant', 'default' => false, 'side' => true],
            ['name' => 'image', 'type' => 'image', 'label' => 'Image', 'side' => true],
            static::slugField(),
            static::seoField(),
        ];
    }

    public static function columns(): array
    {
        return [
            ['name' => 'image', 'label' => '', 'type' => 'image'],
            ['name' => 'title', 'label' => 'Offre', 'type' => 'title'],
            ['name' => 'category', 'label' => 'Catégorie', 'type' => 'text', 'value' => fn ($m) => $m->category?->pathLabel()],
            ['name' => 'is_featured', 'label' => 'En avant', 'type' => 'bool'],
            ['name' => 'is_published', 'label' => 'Publiée', 'type' => 'bool'],
        ];
    }

    public static function query(): Builder
    {
        return Solution::query()->with('category.parent')->orderBy('sort')->orderBy('id');
    }

    public static function filters(): array
    {
        return ['category' => ['label' => 'Catégorie', 'options' => Fields::optionsFrom('solution_categories')]];
    }

    public static function applyFilters(Builder $query, array $filters): Builder
    {
        return $query->when($filters['category'] ?? null, fn ($q, $id) => $q->where('solution_category_id', $id));
    }
}
