{{-- Fil d'Ariane : Accueil › (parents) › page courante --}}
@php $trail = $trail ?? []; @endphp
<nav aria-label="Fil d'Ariane" class="text-sm text-white/70">
    <ol class="flex flex-wrap items-center gap-x-2 gap-y-1">
        <li><a href="{{ localized_url() }}" class="hover:text-white">{{ __('site.home') }}</a></li>
        @foreach ($trail as $label => $url)
            <li class="flex items-center gap-2"><x-site.icon name="chevron-right" class="size-3.5 opacity-60" /><a href="{{ $url }}" class="hover:text-white">{{ $label }}</a></li>
        @endforeach
        <li class="flex items-center gap-2" aria-current="page"><x-site.icon name="chevron-right" class="size-3.5 opacity-60" /><span class="text-white">{{ \Illuminate\Support\Str::limit($current, 60) }}</span></li>
    </ol>
</nav>
