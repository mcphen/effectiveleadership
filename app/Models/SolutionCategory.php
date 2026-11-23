<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedSlug;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

/**
 * Pôle (ELC Advisory, ELC Institute) quand parent_id est vide, sinon catégorie d'offres.
 */
class SolutionCategory extends Model
{
    use HasLocalizedSlug, HasTranslations, Publishable;

    protected $fillable = ['parent_id', 'title', 'slug', 'summary', 'image', 'blocks', 'seo', 'is_published', 'sort'];

    public array $translatable = ['title', 'slug', 'summary'];

    protected $casts = ['blocks' => 'array', 'seo' => 'array', 'is_published' => 'boolean'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort')->orderBy('id');
    }

    public function solutions(): HasMany
    {
        return $this->hasMany(Solution::class)->orderBy('sort')->orderBy('id');
    }

    public function isPole(): bool
    {
        return $this->parent_id === null;
    }

    public function url(?string $locale = null): string
    {
        return localized_url(segment('solutions', $locale).'/'.$this->slugFor($locale), $locale);
    }

    /** Libellé « Pôle › Catégorie » pour les listes déroulantes de l'admin. */
    public function pathLabel(): string
    {
        $title = $this->getTranslation('title', default_locale());

        return $this->parent ? $this->parent->getTranslation('title', default_locale()).' › '.$title : $title;
    }
}
