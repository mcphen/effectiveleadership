@php
    $networks = collect(['linkedin' => 'LinkedIn', 'facebook' => 'Facebook', 'instagram' => 'Instagram', 'youtube' => 'YouTube', 'twitter' => 'X'])
        ->filter(fn ($label, $key) => filled(setting("socials.$key")));
@endphp
@if ($networks->isNotEmpty())
    <ul class="flex flex-wrap gap-3 {{ $class ?? '' }}">
        @foreach ($networks as $key => $label)
            <li>
                <a href="{{ setting("socials.$key") }}" target="_blank" rel="noopener" aria-label="{{ $label }}"
                   class="flex size-10 items-center justify-center rounded-full border transition {{ ! empty($light) ? 'border-white/20 text-white/80 hover:border-white hover:bg-white hover:text-ink' : 'border-slate-300 text-slate-600 hover:border-brand hover:bg-brand hover:text-white' }}">
                    <x-site.icon :name="$key" class="size-[18px]" />
                </a>
            </li>
        @endforeach
    </ul>
@endif
