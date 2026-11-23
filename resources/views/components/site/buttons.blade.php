{{-- Liste de boutons saisis dans l'admin : [{label, url, style}] --}}
@props(['buttons' => [], 'class' => ''])
@php
    $buttons = collect($buttons)->filter(fn ($b) => tr($b['label'] ?? null) !== '');
@endphp
@if ($buttons->isNotEmpty())
    <div class="flex flex-wrap items-center gap-4 {{ $class }}">
        @foreach ($buttons as $button)
            @php
                $href = resolve_link($button['url'] ?? '');
                $external = is_external_link($href);
            @endphp
            <a href="{{ $href }}" class="btn btn-{{ $button['style'] ?? 'primary' }}" @if ($external) target="_blank" rel="noopener" @endif>
                {{ tr($button['label']) }}
                <x-site.icon :name="$external ? 'arrow-up-right' : 'arrow-right'" />
            </a>
        @endforeach
    </div>
@endif
