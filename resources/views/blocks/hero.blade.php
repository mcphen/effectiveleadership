@php
    $heights = [
        'small' => 'min-h-[22rem] md:min-h-[26rem]',
        'medium' => 'min-h-[30rem] md:min-h-[36rem]',
        'large' => 'min-h-[34rem] md:min-h-[44rem]',
        'full' => 'min-h-[calc(100svh-var(--header-h))]',
    ];
    $center = ($d['align'] ?? 'left') === 'center';
    $overlay = max(0, min(90, (int) ($d['overlay'] ?? 55))) / 100;
    $tag = $first ? 'h1' : 'h2';
@endphp
<div class="tone-dark relative isolate flex items-center overflow-hidden bg-ink {{ $heights[$d['height'] ?? 'large'] ?? $heights['large'] }}">
    @if (! empty($d['image']))
        <x-site.img :src="$d['image']" :eager="$first" class="absolute inset-0 -z-20 h-full w-full object-cover" />
        <div class="absolute inset-0 -z-10" style="background: linear-gradient(100deg, rgb(var(--ink) / {{ min(1, $overlay + 0.25) }}) 0%, rgb(var(--ink) / {{ $overlay }}) 55%, rgb(var(--ink) / {{ max(0, $overlay - 0.2) }}) 100%);"></div>
    @else
        <div class="elc-pattern absolute inset-0 -z-10"></div>
        <div class="absolute -right-40 -top-40 -z-10 size-[34rem] rounded-full bg-brand2/25 blur-3xl"></div>
        <div class="absolute -bottom-48 left-1/4 -z-10 size-[28rem] rounded-full bg-brand/40 blur-3xl"></div>
    @endif

    <div class="container-elc py-20 md:py-28">
        <div class="{{ $center ? 'mx-auto max-w-5xl text-center' : 'max-w-5xl' }}">
            @if (tr($d['eyebrow'] ?? null))
                <p class="elc-eyebrow reveal {{ $center ? 'justify-center' : '' }}">{{ tr($d['eyebrow']) }}</p>
            @endif
            <{{ $tag }} class="reveal font-heading text-[2rem] font-bold leading-[1.15] text-white sm:text-4xl md:text-5xl lg:text-[3.4rem]">{!! nl2br(e(tr($d['title'] ?? null))) !!}</{{ $tag }}>
            @if (tr($d['text'] ?? null))
                <p class="reveal mt-6 text-lg leading-relaxed text-white/85 md:text-xl {{ $center ? 'mx-auto max-w-2xl' : 'max-w-2xl' }}">{!! nl2br(e(tr($d['text']))) !!}</p>
            @endif
            <x-site.buttons :buttons="$d['buttons'] ?? []" class="reveal mt-10 {{ $center ? 'justify-center' : '' }}" />
        </div>
    </div>
    <div class="absolute bottom-0 left-0 h-1 w-full bg-gradient-to-r from-accent2 via-brand2 to-brand"></div>
</div>
