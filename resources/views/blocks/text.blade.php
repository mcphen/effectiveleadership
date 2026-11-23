@php
    $widths = ['narrow' => 'max-w-3xl', 'normal' => 'max-w-5xl', 'wide' => 'max-w-none'];
    $center = ($d['align'] ?? 'left') === 'center';
@endphp
<div class="container-elc">
    <div class="{{ $widths[$d['width'] ?? 'narrow'] ?? 'max-w-3xl' }} {{ $center ? 'mx-auto text-center' : '' }}">
        <x-site.heading :eyebrow="$d['eyebrow'] ?? null" :title="$d['title'] ?? null" :intro="$d['intro'] ?? null" :align="$center ? 'center' : 'left'" class="!mb-8" />
        @if (tr($d['content'] ?? null))
            <div class="elc-prose reveal {{ $center ? 'mx-auto' : '' }}">{!! \App\Cms\Html::render(tr($d['content'])) !!}</div>
        @endif
        <x-site.buttons :buttons="$d['buttons'] ?? []" class="reveal mt-10 {{ $center ? 'justify-center' : '' }}" />
    </div>
</div>
