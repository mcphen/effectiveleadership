<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\CaseStudy;
use App\Models\JobOffer;
use App\Models\Page;
use App\Models\Post;
use App\Models\Solution;
use App\Models\SolutionCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SiteController extends Controller
{
    /** « / » : redirige vers la langue principale du site (le sélecteur de langue propose les autres). */
    public function root(): RedirectResponse
    {
        return redirect()->to(localized_url('', default_locale()));
    }

    public function home(): View
    {
        $page = $this->visible(Page::query())->where('is_home', true)->first()
            ?? $this->visible(Page::query())->ordered()->firstOrFail();

        return $this->render('site.page', $page);
    }

    public function page(string $slug): View
    {
        $page = $this->visible(Page::query())->whereSlug($slug)->firstOrFail();

        return $this->render('site.page', $page);
    }

    public function section(string $segment, string $slug, ?string $child = null): View
    {
        $key = collect(config('elc.segments'))->search(fn ($translations) => ($translations[app()->getLocale()] ?? null) === $segment);

        return match ($key) {
            'solutions' => $this->solutions($slug, $child),
            'case_studies' => $child ? abort(404) : $this->render('site.case-study', $this->visible(CaseStudy::query())->whereSlug($slug)->firstOrFail()),
            'posts' => $child ? abort(404) : $this->render('site.post', $this->visible(Post::query())->whereSlug($slug)->firstOrFail()),
            'jobs' => $child ? abort(404) : $this->render('site.job', $this->visible(JobOffer::query())->whereSlug($slug)->firstOrFail()),
            default => abort(404),
        };
    }

    protected function solutions(string $slug, ?string $child): View
    {
        if ($child) {
            $category = $this->visible(SolutionCategory::query())->whereSlug($slug)->firstOrFail();
            $solution = $this->visible(Solution::query())->where('solution_category_id', $category->id)->whereSlug($child)->firstOrFail();

            return $this->render('site.solution', $solution);
        }

        $category = $this->visible(SolutionCategory::query())->whereSlug($slug)->first();

        if ($category) {
            return $this->render('site.category', $category);
        }

        // Offre sans catégorie.
        return $this->render('site.solution', $this->visible(Solution::query())->whereNull('solution_category_id')->whereSlug($slug)->firstOrFail());
    }

    /** Les brouillons restent visibles pour un utilisateur connecté (aperçu). */
    protected function visible(Builder $query): Builder
    {
        if (auth()->check()) {
            return $query;
        }

        return $query->published();
    }

    protected function render(string $view, Model $entity): View
    {
        $seo = (array) ($entity->seo ?? []);
        $title = tr($seo['title'] ?? null) ?: $entity->title;
        $description = tr($seo['description'] ?? null)
            ?: ($entity->summary ?? $entity->excerpt ?? null)
            ?: tr(setting('seo.description'));

        $isDraft = ($entity->status ?? null) === 'draft' || ($entity->is_published ?? true) === false;

        return view($view, [
            'entity' => $entity,
            'blocks' => $entity->blocks ?? [],
            'meta' => [
                'title' => $entity instanceof Page && $entity->is_home ? (setting('identity.site_name') ?: config('app.name')) : $title.setting('seo.title_suffix', ' | ELC'),
                'description' => str(strip_tags((string) $description))->squish()->limit(160)->toString(),
                'image' => media_url($seo['image'] ?? ($entity->image ?? $entity->cover ?? null)) ?: media_url(setting('seo.image')),
                'noindex' => ! empty($seo['noindex']) || $isDraft,
                'canonical' => $entity->url(),
                'alternates' => $entity->alternateUrls(),
            ],
            'isDraft' => $isDraft,
            'editUrl' => auth()->check() ? $this->editUrl($entity) : null,
        ]);
    }

    protected function editUrl(Model $entity): ?string
    {
        $resource = match (true) {
            $entity instanceof Page => 'pages',
            $entity instanceof SolutionCategory => 'solution-categories',
            $entity instanceof Solution => 'solutions',
            $entity instanceof CaseStudy => 'case-studies',
            $entity instanceof Post => 'posts',
            $entity instanceof JobOffer => 'jobs',
            default => null,
        };

        return $resource ? route('admin.resources.edit', [$resource, $entity->getKey()]) : null;
    }
}
