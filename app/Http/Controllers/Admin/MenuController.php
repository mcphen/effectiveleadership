<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaseStudy;
use App\Models\JobOffer;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Post;
use App\Models\Solution;
use App\Models\SolutionCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    public function edit(?string $location = null): Response
    {
        $location ??= array_key_first(Menu::LOCATIONS);
        abort_unless(array_key_exists($location, Menu::LOCATIONS), 404);

        $menu = Menu::query()->firstOrCreate(['location' => $location], ['name' => Menu::LOCATIONS[$location], 'items' => []]);

        return Inertia::render('admin/Menus', [
            'locations' => Menu::LOCATIONS,
            'location' => $location,
            'items' => $menu->items ?? [],
            'maxDepth' => $location === 'header' ? 2 : 1,
        ]);
    }

    public function update(Request $request, string $location): RedirectResponse
    {
        abort_unless(array_key_exists($location, Menu::LOCATIONS), 404);
        $request->validate(['items' => ['present', 'array']]);

        Menu::query()->updateOrCreate(
            ['location' => $location],
            ['name' => Menu::LOCATIONS[$location], 'items' => $this->clean($request->input('items', []), $location === 'header' ? 2 : 1)],
        );

        return back()->with('success', 'Menu enregistré.');
    }

    protected function clean(array $items, int $depth): array
    {
        return collect($items)->filter(fn ($i) => is_array($i))->map(fn ($item) => [
            '_id' => Str::limit((string) ($item['_id'] ?? Str::random(8)), 32, ''),
            'label' => collect(locales())->mapWithKeys(fn ($l, $loc) => [$loc => Str::limit(strip_tags((string) ($item['label'][$loc] ?? '')), 80, '')])->all(),
            'link' => Str::limit(trim((string) ($item['link'] ?? '')), 500, ''),
            'new_tab' => (bool) ($item['new_tab'] ?? false),
            'description' => collect(locales())->mapWithKeys(fn ($l, $loc) => [$loc => Str::limit(strip_tags((string) ($item['description'][$loc] ?? '')), 160, '')])->all(),
            'children' => $depth > 1 ? $this->clean((array) ($item['children'] ?? []), $depth - 1) : [],
        ])->values()->all();
    }

    /**
     * Liste des contenus internes vers lesquels on peut créer un lien
     * (utilisée par le sélecteur de liens partout dans l'admin).
     */
    public function linkTargets(): array
    {
        $fr = default_locale();
        $map = fn ($items, string $prefix, string $group, ?callable $label = null) => $items->map(fn ($m) => [
            'value' => "$prefix:{$m->id}",
            'label' => $label ? $label($m) : $m->getTranslation('title', $fr),
            'group' => $group,
            'url' => $m->url($fr),
        ]);

        return collect()
            ->concat($map(Page::query()->ordered()->get(), 'page', 'Pages'))
            ->concat($map(SolutionCategory::query()->with('parent')->orderBy('parent_id')->orderBy('sort')->get(), 'category', 'Pôles & catégories', fn ($m) => $m->pathLabel()))
            ->concat($map(Solution::query()->ordered()->get(), 'solution', 'Offres'))
            ->concat($map(CaseStudy::query()->ordered()->get(), 'case', 'Réalisations'))
            ->concat($map(Post::query()->ordered()->limit(100)->get(), 'post', 'Publications'))
            ->concat($map(JobOffer::query()->ordered()->get(), 'job', 'Offres d\'emploi'))
            ->values()->all();
    }
}
