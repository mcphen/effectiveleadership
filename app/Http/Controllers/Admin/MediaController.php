<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class MediaController extends Controller
{
    /** Types de fichiers acceptés dans la médiathèque. */
    public const MIMES = 'jpg,jpeg,png,gif,webp,svg,ico,pdf,doc,docx,ppt,pptx,xls,xlsx,mp4';

    public function index(Request $request): Response|JsonResponse
    {
        $query = Media::query()->latest('id')
            ->when($request->query('q'), fn ($q, $s) => $q->where('filename', 'like', "%$s%"))
            ->when($request->query('type') === 'image', fn ($q) => $q->where('mime', 'like', 'image/%'))
            ->when($request->query('type') === 'file', fn ($q) => $q->where('mime', 'not like', 'image/%'));

        $paginator = $query->paginate(48)->withQueryString();
        $items = collect($paginator->items())->map(fn (Media $m) => $this->present($m));

        // Le sélecteur de médias (modale) appelle cette route en JSON.
        if ($request->wantsJson() && ! $request->header('X-Inertia')) {
            return response()->json(['data' => $items, 'next' => $paginator->nextPageUrl()]);
        }

        return Inertia::render('admin/Media', [
            'items' => $items,
            'pagination' => ['links' => $paginator->linkCollection()->toArray(), 'total' => $paginator->total()],
            'query' => ['q' => $request->query('q'), 'type' => $request->query('type')],
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'files' => ['required', 'array', 'max:20'],
            'files.*' => ['file', 'mimes:'.self::MIMES, 'max:'.config('elc.upload_max_kb')],
        ], [], ['files.*' => 'fichier']);

        $created = collect($request->file('files'))->map(fn (UploadedFile $file) => $this->present(static::storeUpload($file)));

        if ($request->wantsJson() && ! $request->header('X-Inertia')) {
            return response()->json(['data' => $created]);
        }

        return back()->with('success', $created->count() > 1 ? $created->count().' fichiers ajoutés.' : 'Fichier ajouté.');
    }

    public function update(Request $request, Media $media): JsonResponse|RedirectResponse
    {
        $data = $request->validate(['alt' => ['nullable', 'array'], 'alt.*' => ['nullable', 'string', 'max:255']]);
        $media->replaceTranslations('alt', array_filter($data['alt'] ?? []));
        $media->save();

        if ($request->wantsJson() && ! $request->header('X-Inertia')) {
            return response()->json(['data' => $this->present($media)]);
        }

        return back()->with('success', 'Texte alternatif enregistré.');
    }

    public function destroy(Media $media): RedirectResponse
    {
        $media->delete();

        return back()->with('success', 'Fichier supprimé.');
    }

    public static function storeUpload(UploadedFile $file): Media
    {
        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'fichier';
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $path = $file->storeAs('media/'.now()->format('Y/m'), $name.'-'.Str::lower(Str::random(6)).'.'.$extension, 'public');

        [$width, $height] = str_starts_with((string) $file->getMimeType(), 'image/') && $extension !== 'svg'
            ? (@getimagesize($file->getRealPath()) ?: [null, null])
            : [null, null];

        return Media::query()->create([
            'disk' => 'public',
            'path' => $path,
            'filename' => $file->getClientOriginalName(),
            'mime' => $extension === 'svg' ? 'image/svg+xml' : $file->getMimeType(),
            'size' => $file->getSize(),
            'width' => $width,
            'height' => $height,
        ]);
    }

    protected function present(Media $media): array
    {
        return [
            'id' => $media->id,
            'path' => $media->path,
            'url' => $media->url,
            'filename' => $media->filename,
            'mime' => $media->mime,
            'is_image' => $media->is_image,
            'size' => $media->size,
            'width' => $media->width,
            'height' => $media->height,
            'alt' => collect(locales())->mapWithKeys(fn ($l, $locale) => [$locale => $media->getTranslation('alt', $locale, false)])->all(),
            'created_at' => $media->created_at?->format('d/m/Y'),
        ];
    }
}
