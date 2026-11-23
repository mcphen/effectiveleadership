<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Page extends Model
{
    use HasLocalizedSlug, HasTranslations;

    protected $fillable = ['title', 'slug', 'blocks', 'seo', 'status', 'is_home', 'sort'];

    public array $translatable = ['title', 'slug'];

    protected $casts = ['blocks' => 'array', 'seo' => 'array', 'is_home' => 'boolean'];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('is_home')->orderBy('sort')->orderBy('id');
    }

    public function url(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $this->is_home ? localized_url('', $locale) : localized_url($this->slugFor($locale), $locale);
    }

    protected static function booted(): void
    {
        // Une seule page d'accueil à la fois.
        static::saved(function (Page $page) {
            if ($page->is_home) {
                static::query()->whereKeyNot($page->getKey())->where('is_home', true)->update(['is_home' => false]);
            }
        });
    }
}
