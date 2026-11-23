@php
    $cols = ['2' => 'sm:grid-cols-2', '3' => 'sm:grid-cols-2 lg:grid-cols-3', '4' => 'sm:grid-cols-2 lg:grid-cols-4'][$d['columns'] ?? '3'] ?? 'sm:grid-cols-3';
    $items = collect($d['items'] ?? [])->filter(fn ($i) => ! empty($i['image']))->values();
@endphp
<div class="container-elc">
    <x-site.heading :eyebrow="$d['eyebrow'] ?? null" :title="$d['title'] ?? null" :intro="$d['intro'] ?? null" />
    <div class="grid gap-5 {{ $cols }}">
        @foreach ($items as $item)
            <figure class="reveal group overflow-hidden" style="border-radius: calc(var(--radius) * 2)">
                <div class="overflow-hidden">
                    <x-site.img :src="$item['image']" :alt="tr($item['caption'] ?? null)" class="aspect-[4/3] w-full object-cover transition duration-500 group-hover:scale-105" />
                </div>
                @if (tr($item['caption'] ?? null))
                    <figcaption class="elc-muted pt-3 text-sm">{{ tr($item['caption']) }}</figcaption>
                @endif
            </figure>
        @endforeach
    </div>
</div>
