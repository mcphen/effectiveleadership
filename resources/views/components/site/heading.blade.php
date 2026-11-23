{{-- Sur-titre + titre + introduction d'une section --}}
@props(['eyebrow' => null, 'title' => null, 'intro' => null, 'align' => 'left', 'tag' => 'h2'])
@php
    $eyebrow = tr($eyebrow);
    $title = tr($title);
    $intro = tr($intro);
@endphp
@if ($eyebrow || $title || $intro)
    <div {{ $attributes->class(['reveal mb-12 max-w-3xl md:mb-14', 'mx-auto text-center' => $align === 'center']) }}>
        @if ($eyebrow)
            <p class="elc-eyebrow {{ $align === 'center' ? 'justify-center' : '' }}">{{ $eyebrow }}</p>
        @endif
        @if ($title)
            <{{ $tag }} class="elc-title">{{ $title }}</{{ $tag }}>
        @endif
        @if ($intro)
            <p class="elc-lead mt-5">{!! nl2br(e($intro)) !!}</p>
        @endif
    </div>
@endif
