@php
    $imageLeft = ($d['image_position'] ?? 'right') === 'left';
    $style = $d['image_style'] ?? 'frame';
@endphp
<div class="container-elc">
    <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
        <div class="reveal {{ $imageLeft ? 'lg:order-2' : '' }}">
            @if (tr($d['eyebrow'] ?? null))
                <p class="elc-eyebrow">{{ tr($d['eyebrow']) }}</p>
            @endif
            @if (tr($d['title'] ?? null))
                <h2 class="elc-title">{{ tr($d['title']) }}</h2>
            @endif
            @if (tr($d['content'] ?? null))
                <div class="elc-prose mt-6">{!! \App\Cms\Html::render(tr($d['content'])) !!}</div>
            @endif
            <x-site.buttons :buttons="$d['buttons'] ?? []" class="mt-9" />
        </div>
        <div class="reveal relative {{ $imageLeft ? 'lg:order-1' : '' }}">
            @if (! empty($d['image']))
                @if ($style === 'frame')
                    <div class="absolute -bottom-5 {{ $imageLeft ? '-left-5' : '-right-5' }} h-full w-full border-2 border-accent2/70" style="border-radius: calc(var(--radius) * 2)"></div>
                @endif
                <x-site.img :src="$d['image']" :alt="tr($d['title'] ?? null)" class="relative aspect-[4/3] w-full object-cover {{ $style === 'plain' ? '' : 'shadow-xl' }}" style="border-radius: calc(var(--radius) * 2)" />
            @else
                <div class="elc-pattern-dark relative flex aspect-[4/3] items-center justify-center bg-brand/[0.06]" style="border-radius: calc(var(--radius) * 2)">
                    <img src="{{ media_url(setting('identity.logo')) ?: asset('images/elc-logo-blue.png') }}" alt="" class="w-3/5 opacity-90 mix-blend-multiply">
                </div>
            @endif
        </div>
    </div>
</div>
