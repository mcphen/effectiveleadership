<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedSlug;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

/** Offre (AXIOME™, Baobab Leadership Immersion™…). */
class Solution extends Model
{
    use HasLocalizedSlug, HasTranslations, Publishable;

    protected $fillable = ['solution_category_id', 'title', 'slug', 'subtitle', 'signature', 'summary', 'image', 'blocks', 'seo', 'is_published', 'is_featured', 'sort'];

    public array $translatable = ['title', 'slug', 'subtitle', 'signature', 'summary'];

    protected $casts = ['blocks' => 'array', 'seo' => 'array', 'is_published' => 'boolean', 'is_featured' => 'boolean'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(SolutionCategory::class, 'solution_category_id');
    }

    public function url(?string $locale = null): string
    {
        $category = $this->category;
        $path = segment('solutions', $locale).'/'.($category ? $category->slugFor($locale).'/' : '').$this->slugFor($locale);

        return localized_url($path, $locale);
    }
}
