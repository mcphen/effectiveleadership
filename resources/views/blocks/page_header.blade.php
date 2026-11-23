@php
    $title = tr($d['title'] ?? null) ?: ($context?->title ?? '');
    $overlay = max(0, min(90, (int) ($d['overlay'] ?? 60))) / 100;
    $tag = $first ? 'h1' : 'h2';
@endphp
<div class="tone-dark relative isolate overflow-hidden bg-ink">
    @if (! empty($d['image']))
        <x-site.img :src="$d['image']" :eager="$first" class="absolute inset-0 -z-20 h-full w-full object-cover" />
        <div class="absolute inset-0 -z-10" style="background: rgb(var(--ink) / {{ $overlay }})"></div>
    @else
        <div class="elc-pattern absolute inset-0 -z-10"></div>
        <div class="absolute -right-32 -top-48 -z-10 size-[30rem] rounded-full bg-brand/50 blur-3xl"></div>
    @endif
    <div class="container-elc py-16 md:py-24">
        @include('site.partials.breadcrumbs', ['current' => $title])
        @if (tr($d['eyebrow'] ?? null))
            <p class="elc-eyebrow mt-6">{{ tr($d['eyebrow']) }}</p>
        @endif
        <{{ $tag }} class="mt-4 max-w-4xl font-heading text-4xl font-bold leading-tight text-white md:text-5xl">{{ $title }}</{{ $tag }}>
        @if (tr($d['text'] ?? null))
            <p class="mt-5 max-w-2xl text-lg text-white/80 md:text-xl">{!! nl2br(e(tr($d['text']))) !!}</p>
        @endif
    </div>
    <div class="absolute bottom-0 left-0 h-1 w-full bg-gradient-to-r from-accent2 via-brand2 to-brand"></div>
</div>
