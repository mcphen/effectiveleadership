<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\CaseStudy;
use App\Models\JobOffer;
use App\Models\Page;
use App\Models\Post;
use App\Models\Solution;
use App\Models\SolutionCategory;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $entries = collect()
            ->concat(Page::query()->published()->get())
            ->concat(SolutionCategory::query()->published()->get())
            ->concat(Solution::query()->published()->with('category')->get())
            ->concat(CaseStudy::query()->published()->get())
            ->concat(Post::query()->published()->get())
            ->concat(JobOffer::query()->open()->get())
            ->reject(fn ($m) => ! empty($m->seo['noindex']))
            ->map(fn ($m) => ['urls' => $m->alternateUrls(), 'updated' => $m->updated_at?->toAtomString()]);

        return response()->view('site.sitemap', ['entries' => $entries])->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        return response("User-agent: *\nDisallow: /admin\nDisallow: /login\n\nSitemap: ".route('sitemap')."\n")
            ->header('Content-Type', 'text/plain');
    }
}
