@php
    $limit = (int) ($d['limit'] ?? 0);
    $items = \App\Models\Testimonial::query()->published()->ordered()->when($limit > 0, fn ($q) => $q->limit($limit))->get();
    $slider = ($d['layout'] ?? 'slider') === 'slider' && $items->count() > 1;
@endphp
@if ($items->isNotEmpty())
    <div class="container-elc">
        <x-site.heading :eyebrow="$d['eyebrow'] ?? null" :title="$d['title'] ?? null" :intro="$d['intro'] ?? null" align="center" />

        @if ($slider)
            <div class="reveal relative mx-auto max-w-4xl" x-data="slider({{ $items->count() }})" @mouseenter="stop()" @mouseleave="play()">
                <div class="grid">
                    @foreach ($items as $i => $t)
                        <figure
                            class="flex flex-col items-center text-center transition duration-500 [grid-area:1/1]"
                            :class="index === {{ $i }} ? 'opacity-100 translate-x-0' : 'pointer-events-none opacity-0 {{ $i > 0 ? 'translate-x-6' : '-translate-x-6' }}'"
                            @if ($i > 0) x-cloak @endif
                            :aria-hidden="index !== {{ $i }}"
                        >
                            @include('site.partials.testimonial', ['t' => $t])
                        </figure>
                    @endforeach
                </div>
                <div class="mt-8 flex items-center justify-center gap-4">
                    <button type="button" class="flex size-10 items-center justify-center rounded-full border border-slate-300 transition hover:border-brand hover:bg-brand hover:text-white" @click="prev(); play()" aria-label="{{ __('site.previous') }}"><x-site.icon name="chevron-left" class="size-5" /></button>
                    <div class="flex gap-2">
                        @foreach ($items as $i => $t)
                            <button type="button" class="h-2 rounded-full transition-all" :class="index === {{ $i }} ? 'w-8 bg-brand' : 'w-2 bg-slate-300'" @click="go({{ $i }})" aria-label="{{ $i + 1 }}"></button>
                        @endforeach
                    </div>
                    <button type="button" class="flex size-10 items-center justify-center rounded-full border border-slate-300 transition hover:border-brand hover:bg-brand hover:text-white" @click="next(); play()" aria-label="{{ __('site.next') }}"><x-site.icon name="chevron-right" class="size-5" /></button>
                </div>
            </div>
        @else
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($items as $t)
                    <figure class="elc-card reveal items-center text-center shadow-sm">
                        @include('site.partials.testimonial', ['t' => $t, 'small' => true])
                    </figure>
                @endforeach
            </div>
        @endif
    </div>
@endif
