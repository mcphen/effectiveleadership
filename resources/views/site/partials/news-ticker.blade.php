{{-- Fil d'actualité défilant (Réglages → Fil d'actualité) : informations saisies + dernières publications. --}}
@php
    $news = collect(setting('news.items', []))
        ->map(fn ($item) => ['text' => tr($item['text'] ?? null), 'url' => resolve_link($item['link'] ?? null)])
        ->filter(fn ($item) => filled($item['text']));

    if (setting('news.posts', true)) {
        $news = $news->concat(
            \App\Models\Post::query()->published()->ordered()->limit(3)->get()
                ->map(fn ($post) => ['text' => $post->getTranslation('title', app()->getLocale()), 'url' => $post->url()])
        );
    }

    $news = $news->values();
@endphp
@if (setting('news.enabled', true) && $news->isNotEmpty())
    <section class="news-ticker relative z-40 flex h-11 items-stretch overflow-hidden bg-brand text-sm text-white" aria-label="{{ tr(setting('news.label')) ?: __('site.news') }}">
        <p class="relative z-10 flex shrink-0 items-center gap-2 bg-black/25 px-4 font-semibold uppercase tracking-[0.12em] text-[12px] md:px-6">
            <x-site.icon name="megaphone" class="size-4 text-accent2" />
            <span class="hidden sm:inline">{{ tr(setting('news.label')) ?: __('site.news') }}</span>
        </p>
        <div class="news-ticker__viewport relative flex-1 overflow-hidden [mask-image:linear-gradient(to_right,transparent,black_3%,black_97%,transparent)]">
            <ul class="news-ticker__track flex h-full w-max items-center" style="--ticker-duration: {{ max(25, $news->count() * 12) }}s">
                @foreach ([...$news, ...$news] as $i => $item)
                    <li class="flex shrink-0 items-center" @if ($i >= $news->count()) aria-hidden="true" @endif>
                        @if ($item['url'])
                            <a href="{{ $item['url'] }}" @if ($i >= $news->count()) tabindex="-1" @endif class="whitespace-nowrap px-6 font-medium underline-offset-4 hover:underline focus-visible:underline">{{ $item['text'] }}</a>
                        @else
                            <span class="whitespace-nowrap px-6 font-medium">{{ $item['text'] }}</span>
                        @endif
                        <span class="size-1.5 shrink-0 rounded-full bg-accent2" aria-hidden="true"></span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endif
