<?php

namespace App\Cms\Resources;

use App\Models\SolutionCategory;
use Illuminate\Database\Eloquent\Builder;

class SolutionCategoryResource extends Resource
{
    public static string $model = SolutionCategory::class;

    public static string $slug = 'solution-categories';

    public static string $label = 'Pôles & catégories';

    public static string $singular = 'catégorie';

    public static bool $feminine = true;

    public static string $icon = 'folder-tree';

    public static function fields(): array
    {
        return [
            ['name' => 'title', 'type' => 'text', 'label' => 'Titre', 't' => true, 'required' => true],
            ['name' => 'summary', 'type' => 'textarea', 'label' => 'Résumé (affiché sur les cartes)', 't' => true, 'rows' => 3],
            ['name' => 'blocks', 'type' => 'blocks', 'label' => 'Contenu de la page', 'help' => 'Les offres de la catégorie s\'affichent automatiquement sous ces blocs.'],
            ['name' => 'parent_id', 'type' => 'select', 'label' => 'Pôle parent', 'options_source' => 'solution_poles', 'placeholder' => '— Aucun (c\'est un pôle) —', 'side' => true],
            static::publishedField(),
            ['name' => 'image', 'type' => 'image', 'label' => 'Image', 'side' => true],
            static::slugField(),
            static::seoField(),
        ];
    }

    public static function columns(): array
    {
        return [
            ['name' => 'title', 'label' => 'Titre', 'type' => 'title'],
            ['name' => 'parent', 'label' => 'Pôle', 'type' => 'text', 'value' => fn ($m) => $m->parent?->getTranslation('title', default_locale()) ?? '— Pôle —'],
            ['name' => 'solutions_count', 'label' => 'Offres', 'type' => 'text', 'value' => fn ($m) => $m->solutions()->count()],
            ['name' => 'is_published', 'label' => 'Publié', 'type' => 'bool'],
        ];
    }

    public static function query(): Builder
    {
        // Pôles puis leurs catégories, dans l'ordre.
        return SolutionCategory::query()->with('parent')
            ->leftJoin('solution_categories as p', 'p.id', '=', 'solution_categories.parent_id')
            ->select('solution_categories.*')
            ->orderByRaw('COALESCE(p.sort, solution_categories.sort), COALESCE(p.id, solution_categories.id), solution_categories.parent_id IS NOT NULL, solution_categories.sort');
    }

    public static function searchable(): array
    {
        return ['solution_categories.title'];
    }
}
