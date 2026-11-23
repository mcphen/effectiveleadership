{{-- En-tête des pages de contenu (offres, catégories, publications…) --}}
@php
    $trail = $trail ?? [];
    $image = $image ?? null;
@endphp
<div class="tone-dark relative isolate overflow-hidden bg-ink">
    @if ($image)
        <x-site.img :src="$image" :eager="true" class="absolute inset-0 -z-20 h-full w-full object-cover" />
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-ink via-ink/90 to-ink/60"></div>
    @else
        <div class="elc-pattern absolute inset-0 -z-10"></div>
        <div class="absolute -right-32 -top-48 -z-10 size-[30rem] rounded-full bg-brand/50 blur-3xl"></div>
        <div class="absolute -bottom-40 left-10 -z-10 size-72 rounded-full bg-brand2/20 blur-3xl"></div>
    @endif
    <div class="container-elc py-14 md:py-20">
        @include('site.partials.breadcrumbs', ['current' => $title, 'trail' => $trail])
        @if (! empty($eyebrow))
            <p class="elc-eyebrow mt-8">{{ $eyebrow }}</p>
        @endif
        <h1 class="{{ empty($eyebrow) ? 'mt-6' : '' }} max-w-4xl font-heading text-4xl font-bold leading-tight text-white md:text-5xl">{{ $title }}</h1>
        @if (! empty($subtitle))
            <p class="mt-5 max-w-3xl text-lg leading-relaxed text-white/80 md:text-xl">{{ $subtitle }}</p>
        @endif
        @if (! empty($signature))
            <p class="mt-6 inline-flex items-center gap-3 font-heading text-lg italic text-accent2"><span class="h-px w-8 bg-accent2"></span>{{ $signature }}</p>
        @endif
        {{ $slot ?? '' }}
        @isset($after)
            {!! $after !!}
        @endisset
    </div>
    <div class="absolute bottom-0 left-0 h-1 w-full bg-gradient-to-r from-accent2 via-brand2 to-brand"></div>
</div>
