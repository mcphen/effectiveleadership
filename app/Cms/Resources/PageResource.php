<?php

namespace App\Cms\Resources;

use App\Models\Page;
use Illuminate\Database\Eloquent\Builder;

class PageResource extends Resource
{
    public static string $model = Page::class;

    public static string $slug = 'pages';

    public static string $label = 'Pages';

    public static string $singular = 'page';

    public static bool $feminine = true;

    public static string $icon = 'file-text';

    public static function fields(): array
    {
        return [
            ['name' => 'title', 'type' => 'text', 'label' => 'Titre de la page', 't' => true, 'required' => true],
            ['name' => 'blocks', 'type' => 'blocks', 'label' => 'Contenu de la page'],
            ['name' => 'status', 'type' => 'select', 'label' => 'Statut', 'options' => ['published' => 'Publiée', 'draft' => 'Brouillon'], 'default' => 'draft', 'side' => true],
            ['name' => 'is_home', 'type' => 'toggle', 'label' => 'Page d\'accueil du site', 'default' => false, 'side' => true],
            static::slugField(),
            static::seoField(),
        ];
    }

    public static function columns(): array
    {
        return [
            ['name' => 'title', 'label' => 'Titre', 'type' => 'title'],
            ['name' => 'status', 'label' => 'Statut', 'type' => 'badge', 'options' => ['published' => 'Publiée', 'draft' => 'Brouillon']],
            ['name' => 'is_home', 'label' => 'Accueil', 'type' => 'bool'],
            ['name' => 'updated_at', 'label' => 'Modifiée le', 'type' => 'date'],
        ];
    }

    public static function query(): Builder
    {
        return Page::query()->ordered();
    }
}
