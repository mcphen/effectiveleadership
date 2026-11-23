{{-- Carte générique : offre, catégorie, réalisation (image facultative, sur-titre, titre, résumé, lien) --}}
@php
    $title = $card['title'] ?? '';
    $href = $card['href'] ?? '#';
    $image = $card['image'] ?? null;
    $meta = $card['meta'] ?? null;
    $summary = $card['summary'] ?? null;
    $linkLabel = $card['linkLabel'] ?? __('site.read_more');
    $delay = $card['delay'] ?? 0;
@endphp
<article class="elc-card group reveal overflow-hidden !p-0 shadow-[0_1px_3px_rgb(0_0_0/0.06),0_8px_24px_-12px_rgb(var(--ink)/0.18)] hover:-translate-y-1 hover:shadow-[0_18px_40px_-16px_rgb(var(--ink)/0.35)]" style="transition-delay: {{ $delay }}ms">
    @if ($image)
        <div class="overflow-hidden">
            <x-site.img :src="$image" :alt="$title" class="aspect-[16/10] w-full object-cover transition duration-500 group-hover:scale-105" />
        </div>
    @else
        <div class="h-1.5 bg-gradient-to-r from-brand via-brand2 to-accent2"></div>
    @endif
    <div class="flex flex-1 flex-col p-7">
        @if ($meta)
            <p class="mb-3 text-xs font-semibold uppercase tracking-[0.14em] text-brand2">{{ $meta }}</p>
        @endif
        <h3 class="font-heading text-xl font-bold leading-snug text-ink">
            <a href="{{ $href }}" class="after:absolute after:inset-0">{{ $title }}</a>
        </h3>
        @if ($summary)
            <p class="mt-3 flex-1 text-[16px] leading-relaxed text-slate-600">{{ \Illuminate\Support\Str::limit(strip_tags($summary), 220) }}</p>
        @endif
        <span class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-brand">
            {{ $linkLabel }} <x-site.icon name="arrow-right" class="size-4 transition group-hover:translate-x-1" />
        </span>
    </div>
</article>
