<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/** Publication ELC Institute : article (Thought Leadership), newsletter ou Action Research. */
class Post extends Model
{
    use HasLocalizedSlug, HasTranslations;

    protected $fillable = ['type', 'title', 'slug', 'excerpt', 'cover', 'attachment', 'author', 'blocks', 'seo', 'is_published', 'published_at'];

    public array $translatable = ['title', 'slug', 'excerpt'];

    protected $casts = ['blocks' => 'array', 'seo' => 'array', 'is_published' => 'boolean', 'published_at' => 'datetime'];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('published_at')->orderByDesc('id');
    }

    public function typeLabel(?string $locale = null): string
    {
        return config("elc.post_types.{$this->type}.".($locale ?? app()->getLocale())) ?? $this->type;
    }

    public function url(?string $locale = null): string
    {
        return localized_url(segment('posts', $locale).'/'.$this->slugFor($locale), $locale);
    }
}
