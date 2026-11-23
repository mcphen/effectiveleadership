{{--
    Affiche une liste de blocs. Chaque bloc est entouré d'une <section> qui applique
    les réglages « Apparence » (fond, espacement, ancre) ; le contenu vient de blocks/{type}.blade.php.
--}}
@php
    $backgrounds = [
        'white' => 'bg-white',
        'light' => 'bg-slate-50',
        'tint' => 'bg-brand/[0.06]',
        'primary' => 'bg-brand tone-dark',
        'dark' => 'bg-ink tone-dark',
    ];
    $spacings = [
        'none' => '',
        'small' => 'py-10 md:py-14',
        'normal' => 'py-16 md:py-24',
        'large' => 'py-24 md:py-32',
    ];
    // Ces blocs gèrent eux-mêmes leur fond (image pleine largeur).
    $fullBleed = ['hero', 'page_header'];
    $visible = collect($blocks ?? [])->filter(fn ($b) => empty($b['hidden']) && \App\Cms\Blocks::exists($b['type'] ?? ''))->values();
@endphp

@foreach ($visible as $index => $block)
    @php
        $s = $block['settings'] ?? [];
        $d = $block['data'] ?? [];
        $bg = $s['background'] ?? 'white';
        $dark = in_array($bg, ['primary', 'dark'], true);
        $isFull = in_array($block['type'], $fullBleed, true);
        $anchor = \Illuminate\Support\Str::slug($s['anchor'] ?? '');
    @endphp
    <section
        @if ($anchor) id="{{ $anchor }}" @endif
        data-block="{{ $block['type'] }}"
        class="relative {{ $isFull ? '' : ($backgrounds[$bg] ?? 'bg-white').' '.($spacings[$s['spacing'] ?? 'normal'] ?? $spacings['normal']) }}"
    >
        @include('blocks.'.$block['type'], ['d' => $d, 's' => $s, 'dark' => $dark, 'first' => $index === 0, 'context' => $context ?? null])
    </section>
@endforeach
