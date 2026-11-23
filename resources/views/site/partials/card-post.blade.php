<article class="group reveal relative flex flex-col" style="transition-delay: {{ $delay ?? 0 }}ms">
    <div class="relative overflow-hidden bg-brand/[0.07]" style="border-radius: calc(var(--radius) * 2)">
        @if ($post->cover)
            <x-site.img :src="$post->cover" :alt="$post->title" class="aspect-[16/10] w-full object-cover transition duration-500 group-hover:scale-105" />
        @else
            <div class="elc-pattern-dark flex aspect-[16/10] items-center justify-center">
                <x-site.icon :name="$post->type === 'research' ? 'search' : ($post->type === 'newsletter' ? 'mail' : 'file-text')" class="size-12 text-brand/40" />
            </div>
        @endif
        <span class="absolute left-4 top-4 rounded-full bg-white/95 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-brand shadow-sm">{{ $post->typeLabel() }}</span>
    </div>
    <div class="flex flex-1 flex-col pt-5">
        @if ($post->published_at)
            <p class="elc-muted flex items-center gap-2 text-sm"><x-site.icon name="calendar" class="size-4" />{{ $post->published_at->translatedFormat('j F Y') }}</p>
        @endif
        <h3 class="mt-2 font-heading text-xl font-bold leading-snug text-ink transition group-hover:text-brand">
            <a href="{{ $post->url() }}" class="after:absolute after:inset-0">{{ $post->title }}</a>
        </h3>
        @if ($post->excerpt)
            <p class="elc-muted mt-3 line-clamp-3 leading-relaxed">{{ $post->excerpt }}</p>
        @endif
    </div>
</article>
