@php
    $cols = ['1' => '', '2' => 'md:grid-cols-2', '3' => 'md:grid-cols-2 lg:grid-cols-3'][$d['columns'] ?? '2'] ?? 'md:grid-cols-2';
    $items = collect($d['items'] ?? [])->filter(fn ($i) => tr($i['text'] ?? null))->values();
@endphp
<div class="container-elc">
    <x-site.heading :eyebrow="$d['eyebrow'] ?? null" :title="$d['title'] ?? null" :intro="$d['intro'] ?? null" />
    <ul class="grid gap-x-10 gap-y-4 {{ $cols }} {{ ($d['columns'] ?? '2') === '1' ? 'max-w-3xl' : '' }}">
        @foreach ($items as $i => $item)
            <li class="reveal flex items-start gap-4 border-b pb-4 {{ $dark ? 'border-white/15' : 'border-slate-200' }}" style="transition-delay: {{ min($i, 8) * 50 }}ms">
                <span class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full bg-brand2/10 text-brand2 {{ $dark ? '!bg-white/15 !text-white' : '' }}">
                    <x-site.icon name="check" class="size-4" :stroke="2.5" />
                </span>
                <span class="text-[17px] leading-relaxed">{{ tr($item['text']) }}</span>
            </li>
        @endforeach
    </ul>
</div>
