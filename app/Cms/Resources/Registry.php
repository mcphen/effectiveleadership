<?php

namespace App\Cms\Resources;

class Registry
{
    /** @return array<string, class-string<Resource>> */
    public static function all(): array
    {
        return collect([
            PageResource::class,
            SolutionCategoryResource::class,
            SolutionResource::class,
            CaseStudyResource::class,
            ClientReferenceResource::class,
            TestimonialResource::class,
            PostResource::class,
            JobOfferResource::class,
        ])->mapWithKeys(fn ($class) => [$class::$slug => $class])->all();
    }

    /** @return class-string<Resource> */
    public static function find(string $slug): string
    {
        return static::all()[$slug] ?? abort(404);
    }
}
