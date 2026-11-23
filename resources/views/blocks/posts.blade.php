@php
    $perPage = max(1, (int) ($d['limit'] ?? 3));
    $query = \App\Models\Post::query()->published()->ordered()->when($d['type'] ?? null, fn ($q, $type) => $q->where('type', $type));
    $paginate = ! empty($d['paginate']);
    $posts = $paginate ? $query->paginate($perPage, ['*'], 'page')->withQueryString() : $query->limit($perPage)->get();
@endphp
<div class="container-elc">
    <x-site.heading :eyebrow="$d['eyebrow'] ?? null" :title="$d['title'] ?? null" :intro="$d['intro'] ?? null" />

    @if ($posts->isEmpty())
        <p class="elc-muted reveal rounded-lg border border-dashed border-slate-300 px-6 py-12 text-center">{{ __('site.no_posts') }}</p>
    @else
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($posts as $i => $post)
                @include('site.partials.card-post', ['post' => $post, 'delay' => ($i % 3) * 80])
            @endforeach
        </div>
    @endif

    @if ($paginate && $posts->hasPages())
        <nav class="mt-14 flex items-center justify-center gap-2" aria-label="Pagination">
            @if ($posts->previousPageUrl())
                <a href="{{ $posts->previousPageUrl() }}" class="btn btn-outline !px-4"><x-site.icon name="arrow-left" /> {{ __('site.previous') }}</a>
            @endif
            <span class="elc-muted px-4 text-sm">{{ $posts->currentPage() }} / {{ $posts->lastPage() }}</span>
            @if ($posts->nextPageUrl())
                <a href="{{ $posts->nextPageUrl() }}" class="btn btn-outline !px-4">{{ __('site.next') }} <x-site.icon name="arrow-right" /></a>
            @endif
        </nav>
    @endif

    <x-site.buttons :buttons="$d['button'] ?? []" class="reveal mt-12 justify-center" />
</div>
