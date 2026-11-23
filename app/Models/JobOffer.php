<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedSlug;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class JobOffer extends Model
{
    use HasLocalizedSlug, HasTranslations, Publishable;

    protected $fillable = ['title', 'slug', 'location', 'contract_type', 'summary', 'blocks', 'seo', 'closes_at', 'is_published', 'sort'];

    public array $translatable = ['title', 'slug', 'contract_type', 'summary'];

    protected $casts = ['blocks' => 'array', 'seo' => 'array', 'is_published' => 'boolean', 'closes_at' => 'date'];

    public function scopeOpen(Builder $query): Builder
    {
        return $query->published()->where(fn ($q) => $q->whereNull('closes_at')->orWhereDate('closes_at', '>=', today()));
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function url(?string $locale = null): string
    {
        return localized_url(segment('jobs', $locale).'/'.$this->slugFor($locale), $locale);
    }
}
