<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Slug traduit (colonne JSON « slug ») : recherche et génération d'URL par langue.
 */
trait HasLocalizedSlug
{
    public function scopeWhereSlug(Builder $query, string $slug, ?string $locale = null): Builder
    {
        $locale ??= app()->getLocale();

        return $query->where(function (Builder $q) use ($slug, $locale) {
            $q->where("slug->$locale", $slug);

            if ($locale !== default_locale()) {
                // Repli : un contenu sans slug anglais reste joignable via son slug français.
                $q->orWhere(fn (Builder $q) => $q
                    ->where("slug->".default_locale(), $slug)
                    ->where(fn (Builder $q) => $q->whereNull("slug->$locale")->orWhere("slug->$locale", '')));
            }
        });
    }

    public function slugFor(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return (string) $this->getTranslation('slug', $locale, true);
    }

    /** @return array<string, string> URL de ce contenu dans chaque langue (pour le sélecteur de langue et hreflang). */
    public function alternateUrls(): array
    {
        return collect(locales())->mapWithKeys(fn ($label, $locale) => [$locale => $this->url($locale)])->all();
    }

    abstract public function url(?string $locale = null): string;
}
