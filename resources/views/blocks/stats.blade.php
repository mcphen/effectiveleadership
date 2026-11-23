@php
    $items = collect($d['items'] ?? [])->filter(fn ($i) => ($i['value'] ?? '') !== '')->values();
    // Classes écrites en entier pour que Tailwind les génère.
    $cols = match ($items->count()) {
        1 => '',
        2 => 'sm:grid-cols-2',
        3 => 'sm:grid-cols-3',
        4 => 'sm:grid-cols-2 lg:grid-cols-4',
        5 => 'sm:grid-cols-3 lg:grid-cols-5',
        default => 'sm:grid-cols-3 lg:grid-cols-6',
    };
@endphp
<div class="container-elc {{ $dark ? 'elc-pattern' : '' }}">
    <x-site.heading :eyebrow="$d['eyebrow'] ?? null" :title="$d['title'] ?? null" :intro="$d['intro'] ?? null" align="center" />
    <dl class="grid gap-10 text-center {{ $cols }}">
        @foreach ($items as $i => $item)
            <div class="reveal" style="transition-delay: {{ $i * 90 }}ms">
                <dd class="font-heading text-5xl font-bold {{ $dark ? 'text-white' : 'text-brand' }} md:text-6xl" x-data="counter(@js((string) $item['value']))" x-text="display">{{ $item['value'] }}</dd>
                <div class="mx-auto my-4 h-0.5 w-10 bg-accent2"></div>
                <dt class="elc-muted text-[15px] font-semibold uppercase tracking-wider">{{ tr($item['label'] ?? null) }}</dt>
            </div>
        @endforeach
    </dl>
</div>
