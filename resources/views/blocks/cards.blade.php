@php
    $cols = ['2' => 'md:grid-cols-2', '3' => 'md:grid-cols-2 lg:grid-cols-3', '4' => 'sm:grid-cols-2 lg:grid-cols-4'][$d['columns'] ?? '3'] ?? 'md:grid-cols-3';
    $style = $d['style'] ?? 'elevated';
    $items = collect($d['items'] ?? [])->filter(fn ($i) => tr($i['title'] ?? null) || tr($i['text'] ?? null))->values();
    $cardClass = match ($style) {
        'bordered' => 'border border-slate-200 hover:border-brand/40',
        'accent' => 'border-t-4 border-brand2 shadow-sm hover:shadow-xl',
        'numbered' => 'border border-slate-200 hover:border-brand/40 hover:shadow-lg',
        default => 'shadow-[0_1px_3px_rgb(0_0_0/0.06),0_8px_24px_-12px_rgb(var(--ink)/0.18)] hover:-translate-y-1 hover:shadow-[0_18px_40px_-16px_rgb(var(--ink)/0.35)]',
    };
@endphp
<div class="container-elc">
    <x-site.heading :eyebrow="$d['eyebrow'] ?? null" :title="$d['title'] ?? null" :intro="$d['intro'] ?? null" />
    <div class="grid gap-6 {{ $cols }}">
        @foreach ($items as $i => $item)
            @php
                $href = ! empty($item['url']) ? resolve_link($item['url']) : null;
                $href = $href === '#' ? null : $href;
            @endphp
            <article class="elc-card group reveal {{ $cardClass }}" style="transition-delay: {{ $i * 80 }}ms">
                @if (! empty($item['image']))
                    <x-site.img :src="$item['image']" :alt="tr($item['title'] ?? null)" class="-mx-7 -mt-7 mb-7 aspect-[16/10] w-[calc(100%+3.5rem)] max-w-none object-cover" style="border-radius: calc(var(--radius) * 2) calc(var(--radius) * 2) 0 0" />
                @elseif ($style === 'numbered')
                    <span class="mb-6 font-heading text-5xl font-bold text-brand/15 transition group-hover:text-brand2/40">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                @elseif (! empty($item['icon']))
                    <span class="mb-6 flex size-14 items-center justify-center bg-brand/[0.08] text-brand transition group-hover:bg-brand group-hover:text-white" style="border-radius: calc(var(--radius) * 2)">
                        <x-site.icon :name="$item['icon']" class="size-7" />
                    </span>
                @endif
                <h3 class="font-heading text-xl font-bold leading-snug text-ink">
                    @if ($href)
                        <a href="{{ $href }}" class="after:absolute after:inset-0">{{ tr($item['title'] ?? null) }}</a>
                    @else
                        {{ tr($item['title'] ?? null) }}
                    @endif
                </h3>
                @if (tr($item['text'] ?? null))
                    <p class="mt-3 flex-1 text-[16px] leading-relaxed text-slate-600">{!! nl2br(e(tr($item['text']))) !!}</p>
                @endif
                @if ($href)
                    <span class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-brand">
                        {{ tr($item['link_label'] ?? null) ?: __('site.read_more') }}
                        <x-site.icon name="arrow-right" class="size-4 transition group-hover:translate-x-1" />
                    </span>
                @endif
            </article>
        @endforeach
    </div>
</div>
