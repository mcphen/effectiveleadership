<?php

namespace App\Cms\Resources;

use App\Cms\Fields;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;

class PostResource extends Resource
{
    public static string $model = Post::class;

    public static string $slug = 'posts';

    public static string $label = 'Publications';

    public static string $singular = 'publication';

    public static bool $feminine = true;

    public static string $icon = 'newspaper';

    public static bool $sortable = false;

    public static function fields(): array
    {
        return [
            ['name' => 'title', 'type' => 'text', 'label' => 'Titre', 't' => true, 'required' => true],
            ['name' => 'excerpt', 'type' => 'textarea', 'label' => 'Chapô / résumé', 't' => true, 'rows' => 3],
            ['name' => 'blocks', 'type' => 'blocks', 'label' => 'Contenu'],
            ['name' => 'type', 'type' => 'select', 'label' => 'Type', 'options_source' => 'post_types', 'default' => 'article', 'required' => true, 'side' => true],
            ['name' => 'is_published', 'type' => 'toggle', 'label' => 'Publiée', 'default' => false, 'side' => true],
            ['name' => 'published_at', 'type' => 'date', 'label' => 'Date de publication', 'side' => true],
            ['name' => 'author', 'type' => 'text', 'label' => 'Auteur', 'side' => true],
            ['name' => 'cover', 'type' => 'image', 'label' => 'Image de couverture', 'side' => true],
            ['name' => 'attachment', 'type' => 'file', 'label' => 'Document à télécharger (PDF)', 'side' => true],
            static::slugField(),
            static::seoField(),
        ];
    }

    public static function columns(): array
    {
        return [
            ['name' => 'cover', 'label' => '', 'type' => 'image'],
            ['name' => 'title', 'label' => 'Titre', 'type' => 'title'],
            ['name' => 'type', 'label' => 'Type', 'type' => 'badge', 'options' => Fields::optionsFrom('post_types')],
            ['name' => 'published_at', 'label' => 'Date', 'type' => 'date'],
            ['name' => 'is_published', 'label' => 'Publiée', 'type' => 'bool'],
        ];
    }

    public static function defaults(): array
    {
        return [...parent::defaults(), 'published_at' => now()->format('Y-m-d')];
    }

    public static function query(): Builder
    {
        return Post::query()->ordered();
    }

    public static function filters(): array
    {
        return ['type' => ['label' => 'Type', 'options' => Fields::optionsFrom('post_types')]];
    }

    public static function applyFilters(Builder $query, array $filters): Builder
    {
        return $query->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type));
    }
}
