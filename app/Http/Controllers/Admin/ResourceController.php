<?php

namespace App\Http\Controllers\Admin;

use App\Cms\Blocks;
use App\Cms\Fields;
use App\Cms\Resources\Registry;
use App\Cms\Resources\Resource;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin générique : un seul contrôleur pour tous les types de contenu décrits dans App\Cms\Resources.
 */
class ResourceController extends Controller
{
    public function index(Request $request, string $resource): Response
    {
        /** @var class-string<Resource> $class */
        $class = Registry::find($resource);
        $search = trim((string) $request->query('q'));
        $filters = (array) $request->query('filters', []);

        $query = $class::query();
        $query = $class::applyFilters($query, $filters);

        if ($search !== '') {
            $query->where(function ($q) use ($class, $search) {
                foreach ($class::searchable() as $column) {
                    $q->orWhereRaw('LOWER('.$column.') LIKE ?', ['%'.mb_strtolower($search).'%']);
                }
            });
        }

        $paginator = $query->paginate($class::$perPage)->withQueryString();

        return Inertia::render('admin/resources/Index', [
            'resource' => $class::meta(),
            'columns' => collect($class::columns())->map(fn ($c) => collect($c)->except('value')->all())->values(),
            'filters' => $class::filters(),
            'rows' => collect($paginator->items())->map(fn ($m) => $class::toRow($m)),
            'pagination' => [
                'current' => $paginator->currentPage(),
                'last' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'links' => $paginator->linkCollection()->toArray(),
            ],
            'query' => ['q' => $search, 'filters' => (object) $filters],
        ]);
    }

    public function create(string $resource): Response
    {
        $class = Registry::find($resource);

        return $this->form($class, $class::defaults() + ['id' => null]);
    }

    public function store(Request $request, string $resource): RedirectResponse
    {
        $class = Registry::find($resource);
        $data = $this->validated($request, $class);

        $model = $class::newModel();
        $class::fill($model, $data);
        $model->save();

        return redirect()->route('admin.resources.edit', [$resource, $model->getKey()])
            ->with('success', ucfirst($class::$singular).' '.($class::$feminine ? 'créée' : 'créé').'.');
    }

    public function edit(string $resource, int $id): Response
    {
        $class = Registry::find($resource);
        $model = $class::$model::query()->findOrFail($id);

        return $this->form($class, $class::toForm($model), $model);
    }

    public function update(Request $request, string $resource, int $id): RedirectResponse
    {
        $class = Registry::find($resource);
        $model = $class::$model::query()->findOrFail($id);
        $data = $this->validated($request, $class);

        $class::fill($model, $data);
        $model->save();

        return back()->with('success', 'Modifications enregistrées.');
    }

    public function destroy(string $resource, int $id): RedirectResponse
    {
        $class = Registry::find($resource);
        $class::$model::query()->findOrFail($id)->delete();

        return redirect()->route('admin.resources.index', $resource)
            ->with('success', ucfirst($class::$singular).' '.($class::$feminine ? 'supprimée' : 'supprimé').'.');
    }

    public function duplicate(string $resource, int $id): RedirectResponse
    {
        $class = Registry::find($resource);
        $original = $class::$model::query()->findOrFail($id);
        $copy = $original->replicate();

        // Titre « (copie) » et nouveaux slugs ; une copie n'est jamais publiée ni page d'accueil.
        $data = $class::toForm($original);
        $title = $data[$class::$titleField];
        $data[$class::$titleField] = is_array($title)
            ? collect($title)->map(fn ($v) => $v ? $v.' (copie)' : $v)->all()
            : $title.' (copie)';
        if (array_key_exists('slug', $data)) {
            $data['slug'] = collect(locales())->map(fn () => '')->all();
        }
        $class::fill($copy, $data);
        foreach (['is_published' => false, 'is_home' => false, 'status' => 'draft'] as $attr => $value) {
            if (array_key_exists($attr, $copy->getAttributes())) {
                $copy->setAttribute($attr, $value);
            }
        }
        $copy->save();

        return redirect()->route('admin.resources.edit', [$resource, $copy->getKey()])
            ->with('success', 'Copie créée (non publiée).');
    }

    public function reorder(Request $request, string $resource): RedirectResponse
    {
        $class = Registry::find($resource);
        abort_unless($class::$sortable, 404);
        $ids = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']])['ids'];

        DB::transaction(function () use ($class, $ids) {
            foreach ($ids as $position => $id) {
                $class::$model::query()->whereKey($id)->update(['sort' => $position + 1]);
            }
        });

        return back()->with('success', 'Ordre enregistré.');
    }

    /** @param class-string<Resource> $class */
    protected function form(string $class, array $values, $model = null): Response
    {
        $hasBlocks = collect($class::fields())->contains(fn ($f) => $f['type'] === 'blocks');

        return Inertia::render('admin/resources/Form', [
            'resource' => $class::meta(),
            'fields' => Fields::resolveOptions($class::fields()),
            'values' => $values,
            'blocks' => $hasBlocks ? Blocks::forAdmin() : [],
            'blockGroups' => Blocks::GROUPS,
            'blockSettings' => $hasBlocks ? Blocks::settingsFields() : [],
            'publicUrl' => $model && method_exists($model, 'url') ? $model->url(default_locale()) : null,
            'publicUrls' => $model && method_exists($model, 'alternateUrls') ? $model->alternateUrls() : null,
        ]);
    }

    /** @param class-string<Resource> $class */
    protected function validated(Request $request, string $class): array
    {
        $fields = $class::fields();

        return $request->validate(Fields::rules($fields), [], Fields::attributes($fields));
    }
}
