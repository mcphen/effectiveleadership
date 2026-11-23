{{-- Image de la médiathèque (chemin relatif) avec chargement différé --}}
@props(['src', 'alt' => '', 'eager' => false])
@if ($src)
    <img src="{{ media_url($src) }}" alt="{{ $alt }}" {{ $attributes }} @if ($eager) fetchpriority="high" @else loading="lazy" decoding="async" @endif>
@endif
