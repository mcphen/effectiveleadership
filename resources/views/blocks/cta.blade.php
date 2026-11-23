<div class="container-elc">
    <div class="relative isolate overflow-hidden {{ ! empty($d['image']) ? 'tone-dark px-8 py-14 md:px-14' : '' }}" @if (! empty($d['image'])) style="border-radius: calc(var(--radius) * 3)" @endif>
        @if (! empty($d['image']))
            <x-site.img :src="$d['image']" class="absolute inset-0 -z-20 h-full w-full object-cover" />
            <div class="absolute inset-0 -z-10 bg-gradient-to-r from-ink/95 to-brand/80"></div>
        @endif
        <div class="reveal flex flex-col items-start justify-between gap-8 lg:flex-row lg:items-center">
            <div class="max-w-2xl">
                <h2 class="font-heading text-3xl font-bold leading-tight md:text-4xl">{{ tr($d['title'] ?? null) }}</h2>
                @if (tr($d['text'] ?? null))
                    <p class="elc-lead mt-4">{!! nl2br(e(tr($d['text']))) !!}</p>
                @endif
            </div>
            <x-site.buttons :buttons="$d['buttons'] ?? []" class="shrink-0" />
        </div>
    </div>
</div>
