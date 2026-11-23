<?php

namespace App\Cms\Resources;

use App\Models\CaseStudy;

class CaseStudyResource extends Resource
{
    public static string $model = CaseStudy::class;

    public static string $slug = 'case-studies';

    public static string $label = 'Réalisations';

    public static string $singular = 'réalisation';

    public static bool $feminine = true;

    public static string $icon = 'award';

    public static function fields(): array
    {
        return [
            ['name' => 'title', 'type' => 'text', 'label' => 'Titre', 't' => true, 'required' => true],
            ['name' => 'summary', 'type' => 'textarea', 'label' => 'Résumé', 't' => true, 'rows' => 3],
            ['name' => 'client', 'type' => 'text', 'label' => 'Client', 'width' => 'half'],
            ['name' => 'year', 'type' => 'text', 'label' => 'Année', 'width' => 'half'],
            ['name' => 'sector', 'type' => 'text', 'label' => 'Secteur', 't' => true, 'width' => 'half'],
            ['name' => 'country', 'type' => 'text', 'label' => 'Pays', 't' => true, 'width' => 'half'],
            ['name' => 'blocks', 'type' => 'blocks', 'label' => 'Contenu détaillé'],
            static::publishedField(),
            ['name' => 'image', 'type' => 'image', 'label' => 'Image', 'side' => true],
            static::slugField(),
            static::seoField(),
        ];
    }

    public static function columns(): array
    {
        return [
            ['name' => 'image', 'label' => '', 'type' => 'image'],
            ['name' => 'title', 'label' => 'Titre', 'type' => 'title'],
            ['name' => 'client', 'label' => 'Client', 'type' => 'text'],
            ['name' => 'year', 'label' => 'Année', 'type' => 'text'],
            ['name' => 'is_published', 'label' => 'Publiée', 'type' => 'bool'],
        ];
    }
}
