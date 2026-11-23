<?php

namespace App\Cms\Resources;

use App\Models\ClientReference;

class ClientReferenceResource extends Resource
{
    public static string $model = ClientReference::class;

    public static string $slug = 'references';

    public static string $label = 'Références clients';

    public static string $singular = 'référence';

    public static bool $feminine = true;

    public static string $icon = 'building-2';

    public static string $titleField = 'name';

    public static int $perPage = 100;

    public static function fields(): array
    {
        return [
            ['name' => 'name', 'type' => 'text', 'label' => 'Nom du client', 'required' => true],
            ['name' => 'logo', 'type' => 'image', 'label' => 'Logo'],
            ['name' => 'sector', 'type' => 'text', 'label' => 'Secteur', 't' => true],
            ['name' => 'url', 'type' => 'text', 'label' => 'Site web (facultatif)'],
            static::publishedField(),
        ];
    }

    public static function columns(): array
    {
        return [
            ['name' => 'logo', 'label' => 'Logo', 'type' => 'image'],
            ['name' => 'name', 'label' => 'Client', 'type' => 'title'],
            ['name' => 'sector', 'label' => 'Secteur', 'type' => 'text'],
            ['name' => 'is_published', 'label' => 'Publié', 'type' => 'bool'],
        ];
    }
}
