<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedSlug;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/** Réalisation / étude de cas. */
class CaseStudy extends Model
{
    use HasLocalizedSlug, HasTranslations, Publishable;

    protected $fillable = ['title', 'slug', 'client', 'sector', 'country', 'year', 'summary', 'image', 'blocks', 'seo', 'is_published', 'sort'];

    public array $translatable = ['title', 'slug', 'sector', 'country', 'summary'];

    protected $casts = ['blocks' => 'array', 'seo' => 'array', 'is_published' => 'boolean'];

    public function url(?string $locale = null): string
    {
        return localized_url(segment('case_studies', $locale).'/'.$this->slugFor($locale), $locale);
    }
}
