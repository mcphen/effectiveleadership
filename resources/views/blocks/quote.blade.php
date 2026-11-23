<div class="container-elc">
    <figure class="reveal mx-auto max-w-4xl text-center">
        <x-site.icon name="quote" class="mx-auto size-10 {{ $dark ? 'text-accent2' : 'text-brand2/60' }}" />
        <blockquote class="mt-6 font-heading text-2xl font-bold italic leading-snug {{ $dark ? 'text-white' : 'text-ink' }} md:text-4xl">
            {!! nl2br(e(tr($d['text'] ?? null))) !!}
        </blockquote>
        @if (tr($d['author'] ?? null))
            <figcaption class="mt-6 flex items-center justify-center gap-3 text-sm font-semibold uppercase tracking-widest {{ $dark ? 'text-white/70' : 'text-brand' }}">
                <span class="h-px w-8 bg-accent2"></span>{{ tr($d['author']) }}<span class="h-px w-8 bg-accent2"></span>
            </figcaption>
        @endif
    </figure>
</div>
