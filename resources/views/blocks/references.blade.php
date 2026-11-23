@php
    $items = \App\Models\ClientReference::query()->published()->ordered()->get()->filter(fn ($r) => $r->logo);
    $gray = ! empty($d['grayscale']);
    $imgClass = 'max-h-16 w-auto max-w-[9rem] object-contain transition duration-300 '.($gray ? 'opacity-70 grayscale group-hover:opacity-100 group-hover:grayscale-0' : '');
@endphp
@if ($items->isNotEmpty())
    <div class="container-elc">
        <x-site.heading :eyebrow="$d['eyebrow'] ?? null" :title="$d['title'] ?? null" :intro="$d['intro'] ?? null" align="center" />

        @if (($d['layout'] ?? 'grid') === 'marquee')
            <div class="reveal relative overflow-hidden [mask-image:linear-gradient(to_right,transparent,black_8%,black_92%,transparent)]">
                <ul class="animate-marquee flex w-max items-center gap-16">
                    @foreach ([...$items, ...$items] as $i => $ref)
                        <li class="group flex h-24 shrink-0 items-center" @if ($i >= $items->count()) aria-hidden="true" @endif>
                            <x-site.img :src="$ref->logo" :alt="$ref->name" :title="$ref->name" class="{{ $imgClass }}" />
                        </li>
                    @endforeach
                </ul>
            </div>
        @else
            <ul class="grid grid-cols-2 gap-px overflow-hidden border border-slate-200 bg-slate-200 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6" style="border-radius: calc(var(--radius) * 2)">
                @foreach ($items as $ref)
                    <li class="group flex h-32 items-center justify-center bg-white p-5" title="{{ $ref->name }}">
                        @if ($ref->url)
                            <a href="{{ $ref->url }}" target="_blank" rel="noopener" class="flex h-full w-full items-center justify-center"><x-site.img :src="$ref->logo" :alt="$ref->name" class="{{ $imgClass }}" /></a>
                        @else
                            <x-site.img :src="$ref->logo" :alt="$ref->name" class="{{ $imgClass }}" />
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endif
