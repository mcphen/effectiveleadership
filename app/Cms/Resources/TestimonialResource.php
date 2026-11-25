<?php

namespace App\Cms\Resources;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Model;

class TestimonialResource extends Resource
{
    public static string $model = Testimonial::class;

    public static string $slug = 'testimonials';

    public static string $label = 'Témoignages';

    public static string $singular = 'témoignage';

    public static string $icon = 'message-square-quote';

    public static string $titleField = 'author';

    public static function fields(): array
    {
        return [
            ['name' => 'quote', 'type' => 'textarea', 'label' => 'Témoignage', 't' => true, 'rows' => 5, 'required' => true],
            ['name' => 'author', 'type' => 'text', 'label' => 'Nom', 'required' => true, 'width' => 'half'],
            ['name' => 'company', 'type' => 'text', 'label' => 'Organisation', 'width' => 'half'],
            ['name' => 'role', 'type' => 'text', 'label' => 'Fonction', 't' => true],
            static::publishedField(),
            ['name' => 'photo', 'type' => 'image', 'label' => 'Photo', 'side' => true],
        ];
    }

    public static function columns(): array
    {
        return [
            ['name' => 'photo', 'label' => '', 'type' => 'image'],
            ['name' => 'author', 'label' => 'Nom', 'type' => 'title'],
            ['name' => 'company', 'label' => 'Organisation', 'type' => 'text'],
            ['name' => 'is_published', 'label' => 'Publié', 'type' => 'bool'],
        ];
    }

    public static function toRow(Model $model): array
    {
        return [...parent::toRow($model), '_missing_translation' => blank($model->getTranslations('quote')[translation_locale()] ?? null)];
    }
}
