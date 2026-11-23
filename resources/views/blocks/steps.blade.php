@php
    $items = collect($d['items'] ?? [])->filter(fn ($i) => tr($i['title'] ?? null))->values();
    $timeline = ($d['layout'] ?? 'columns') === 'timeline';
    $cols = match (true) {
        $items->count() >= 4 => 'md:grid-cols-2 lg:grid-cols-4',
        $items->count() === 3 => 'md:grid-cols-3',
        default => 'md:grid-cols-2',
    };
@endphp
<div class="container-elc">
    <x-site.heading :eyebrow="$d['eyebrow'] ?? null" :title="$d['title'] ?? null" :intro="$d['intro'] ?? null" />

    @if ($timeline)
        <ol class="relative max-w-3xl space-y-10 border-l-2 border-brand/15 pl-10">
            @foreach ($items as $i => $item)
                <li class="reveal relative">
                    <span class="absolute -left-[3.4rem] top-0 flex size-10 items-center justify-center rounded-full bg-brand font-heading text-sm font-bold text-white ring-8 ring-white">{{ $i + 1 }}</span>
                    <h3 class="font-heading text-xl font-bold">{{ tr($item['title']) }}</h3>
                    @if (tr($item['text'] ?? null))
                        <p class="elc-muted mt-2 leading-relaxed">{!! nl2br(e(tr($item['text']))) !!}</p>
                    @endif
                </li>
            @endforeach
        </ol>
    @else
        <ol class="relative grid gap-10 {{ $cols }}">
            <div class="absolute left-0 right-0 top-6 hidden h-px bg-gradient-to-r from-brand/0 via-brand/25 to-brand/0 lg:block" aria-hidden="true"></div>
            @foreach ($items as $i => $item)
                <li class="reveal relative" style="transition-delay: {{ $i * 90 }}ms">
                    <span class="relative flex size-12 items-center justify-center rounded-full border-2 border-brand bg-white font-heading text-lg font-bold text-brand shadow-sm">{{ $i + 1 }}</span>
                    <h3 class="mt-6 font-heading text-xl font-bold leading-snug">{{ tr($item['title']) }}</h3>
                    @if (tr($item['text'] ?? null))
                        <p class="elc-muted mt-3 leading-relaxed">{!! nl2br(e(tr($item['text']))) !!}</p>
                    @endif
                </li>
            @endforeach
        </ol>
    @endif
</div>
