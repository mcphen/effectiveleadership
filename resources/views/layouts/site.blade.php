@php
    $siteName = setting('identity.site_name') ?: config('app.name');
    $logo = media_url(setting('identity.logo')) ?: asset('images/elc-logo-blue.png');
    $logoLight = media_url(setting('identity.logo_light'));
    $logoHeight = (int) (setting('identity.logo_height') ?? 52);
    $favicon = media_url(setting('identity.favicon'));
    $meta = $meta ?? [];
    $alternates = $meta['alternates'] ?? collect(locales())->mapWithKeys(fn ($l, $code) => [$code => localized_url('', $code)])->all();
    $header = \App\Cms\Navigation::items('header');
    $footerLinks = \App\Cms\Navigation::items('footer');
    $legalLinks = \App\Cms\Navigation::items('footer_legal');
    $ctaLabel = tr(setting('header.cta_label'));
    $ctaLink = resolve_link(setting('header.cta_link'));
    $phones = collect(setting('contact.phones', []))->pluck('number')->filter();
    $email = setting('contact.email');
    $title = $meta['title'] ?? $siteName;
    $description = $meta['description'] ?? tr(setting('seo.description'));
    $image = $meta['image'] ?? media_url(setting('seo.image'));
    $analytics = setting('seo.analytics');
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    @if ($description)<meta name="description" content="{{ $description }}">@endif
    @if (! empty($meta['noindex']))<meta name="robots" content="noindex, nofollow">@endif
    @if (! empty($meta['canonical']))<link rel="canonical" href="{{ $meta['canonical'] }}">@endif
    @foreach ($alternates as $code => $href)
        <link rel="alternate" hreflang="{{ $code }}" href="{{ $href }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ $alternates[default_locale()] ?? url('/') }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $title }}">
    @if ($description)<meta property="og:description" content="{{ $description }}">@endif
    <meta property="og:url" content="{{ $meta['canonical'] ?? url()->current() }}">
    <meta property="og:locale" content="{{ app()->getLocale() === 'fr' ? 'fr_FR' : 'en_GB' }}">
    @if ($image)<meta property="og:image" content="{{ $image }}"><meta name="twitter:card" content="summary_large_image">@endif

    @if ($favicon)<link rel="icon" href="{{ $favicon }}">@endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{{ \App\Cms\Theme::fontsUrl() }}">
    @vite(['resources/css/site.css', 'resources/js/site.ts'])
    {{-- Après la feuille de style : les couleurs et polices des Réglages remplacent les valeurs par défaut de site.css. --}}
    <style>:root { {!! \App\Cms\Theme::cssVariables() !!} }</style>

    @if ($analytics && preg_match('/^G-[A-Z0-9]+$/', $analytics) && empty($meta['noindex']))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $analytics }}"></script>
        <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','{{ $analytics }}');</script>
    @endif

    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $siteName,
        'url' => localized_url(),
        'logo' => $logo,
        'email' => $email,
        'telephone' => $phones->first(),
        'address' => tr(setting('contact.address')),
        'sameAs' => collect(['linkedin', 'facebook', 'instagram', 'youtube', 'twitter'])->map(fn ($n) => setting("socials.$n"))->filter()->values(),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
</head>
<body class="flex min-h-screen flex-col">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded focus:bg-white focus:px-4 focus:py-2 focus:shadow-lg">{{ __('site.skip') }}</a>

    @if (! empty($isDraft) || ! empty($editUrl))
        <div class="flex items-center justify-center gap-4 bg-amber-400 px-4 py-2 text-center text-sm font-medium text-ink">
            @if (! empty($isDraft)) <span>{{ __('site.draft_notice') }}</span> @endif
            @if (! empty($editUrl)) <a href="{{ $editUrl }}" class="underline underline-offset-2">✎ {{ __('site.edit_page') }}</a> @endif
        </div>
    @endif

    {{-- Barre de contact --}}
    @if (setting('header.topbar', true) && ($email || $phones->isNotEmpty()))
        <div class="hidden bg-ink text-[13px] text-white/75 md:block">
            <div class="container-elc flex h-10 items-center justify-between gap-6">
                <div class="flex items-center gap-6">
                    @if ($phones->isNotEmpty())
                        <a href="tel:{{ preg_replace('/[^\d+]/', '', $phones->first()) }}" class="inline-flex items-center gap-2 hover:text-white"><x-site.icon name="phone" class="size-3.5" />{{ $phones->first() }}</a>
                    @endif
                    @if ($email)
                        <a href="mailto:{{ $email }}" class="inline-flex items-center gap-2 hover:text-white"><x-site.icon name="mail" class="size-3.5" />{{ $email }}</a>
                    @endif
                </div>
                <div class="flex items-center gap-4">
                    @if (tr(setting('identity.tagline')))<span class="hidden italic lg:inline">{{ tr(setting('identity.tagline')) }}</span>@endif
                    @include('site.partials.socials', ['light' => true, 'class' => '!gap-1 [&_a]:!size-7 [&_a]:!border-0'])
                </div>
            </div>
        </div>
    @endif

    {{-- En-tête --}}
    <header x-data="siteHeader" class="sticky top-0 z-50 bg-white/95 backdrop-blur transition-shadow duration-300" :class="scrolled ? 'shadow-[0_6px_30px_-12px_rgb(var(--ink)/0.25)]' : 'border-b border-slate-100'">
        <div class="container-elc flex h-[var(--header-h)] items-center justify-between gap-6">
            <a href="{{ localized_url() }}" class="shrink-0" aria-label="{{ $siteName }} — {{ __('site.home') }}">
                <img src="{{ $logo }}" alt="{{ $siteName }}" style="height: {{ $logoHeight }}px" class="w-auto">
            </a>

            {{-- Navigation bureau --}}
            <nav class="hidden items-center gap-1 lg:flex" aria-label="Navigation principale">
                @foreach ($header as $item)
                    @if ($item['children'])
                        <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @keydown.escape="open = false">
                            <button type="button" class="flex items-center gap-1 px-3 py-2 text-[15px] font-semibold transition {{ $item['active'] ? 'text-brand' : 'text-ink hover:text-brand' }}" @click="open = !open" :aria-expanded="open">
                                {{ $item['label'] }} <span class="transition" :class="open && 'rotate-180'"><x-site.icon name="chevron-down" class="size-4" /></span>
                            </button>
                            <div x-show="open" x-cloak x-transition.opacity.duration.150ms class="absolute left-1/2 top-full w-max min-w-[18rem] max-w-md -translate-x-1/2 pt-3">
                                <div class="overflow-hidden border-t-2 border-brand bg-white p-2 shadow-2xl" style="border-radius: 0 0 calc(var(--radius) * 2) calc(var(--radius) * 2)">
                                    @if ($item['url'] !== '#')
                                        <a href="{{ $item['url'] }}" class="mb-1 block border-b border-slate-100 px-4 pb-3 pt-2 text-xs font-semibold uppercase tracking-[0.15em] text-brand2 hover:text-brand">{{ $item['label'] }} →</a>
                                    @endif
                                    @foreach ($item['children'] as $child)
                                        <a href="{{ $child['url'] }}" @if ($child['new_tab']) target="_blank" rel="noopener" @endif class="block rounded px-4 py-2.5 transition hover:bg-brand/[0.06] {{ $child['active'] ? 'bg-brand/[0.06]' : '' }}">
                                            <span class="block text-[15px] font-semibold {{ $child['active'] ? 'text-brand' : 'text-ink' }}">{{ $child['label'] }}</span>
                                            @if ($child['description'])<span class="mt-0.5 block text-sm text-slate-500">{{ $child['description'] }}</span>@endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ $item['url'] }}" @if ($item['new_tab']) target="_blank" rel="noopener" @endif @if ($item['active']) aria-current="page" @endif
                           class="relative px-3 py-2 text-[15px] font-semibold transition {{ $item['active'] ? 'text-brand' : 'text-ink hover:text-brand' }}">
                            {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
            </nav>

            <div class="flex items-center gap-3">
                {{-- Sélecteur de langue --}}
                <div class="hidden items-center rounded-full border border-slate-200 p-0.5 text-xs font-bold sm:flex" role="navigation" aria-label="{{ __('site.language') }}">
                    @foreach ($alternates as $code => $href)
                        <a href="{{ $href }}" hreflang="{{ $code }}" lang="{{ $code }}" class="rounded-full px-2.5 py-1 uppercase transition {{ $code === app()->getLocale() ? 'bg-brand text-white' : 'text-slate-500 hover:text-brand' }}" @if ($code === app()->getLocale()) aria-current="true" @endif>{{ $code }}</a>
                    @endforeach
                </div>
                @if ($ctaLabel)
                    <a href="{{ $ctaLink }}" class="btn btn-primary hidden !px-5 !py-2.5 xl:inline-flex">{{ $ctaLabel }}</a>
                @endif
                <button type="button" class="flex size-11 items-center justify-center rounded-md text-ink hover:bg-slate-100 lg:hidden" @click="mobileOpen = true" aria-label="{{ __('site.menu') }}" :aria-expanded="mobileOpen">
                    <x-site.icon name="menu" class="size-6" />
                </button>
            </div>
        </div>

        {{-- Menu mobile --}}
        <div x-show="mobileOpen" x-cloak class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-ink/60" x-show="mobileOpen" x-transition.opacity @click="mobileOpen = false"></div>
            <div class="absolute inset-y-0 right-0 flex w-full max-w-sm flex-col bg-white shadow-2xl" x-show="mobileOpen" x-transition:enter="transition duration-300" x-transition:enter-start="translate-x-full" x-transition:leave="transition duration-200" x-transition:leave-end="translate-x-full">
                <div class="flex h-[var(--header-h)] items-center justify-between border-b px-5">
                    <img src="{{ $logo }}" alt="{{ $siteName }}" class="h-10 w-auto">
                    <button type="button" class="flex size-11 items-center justify-center rounded-md hover:bg-slate-100" @click="mobileOpen = false" aria-label="{{ __('site.close') }}"><x-site.icon name="x" class="size-6" /></button>
                </div>
                <nav class="flex-1 overflow-y-auto px-5 py-4" aria-label="Navigation mobile">
                    @foreach ($header as $item)
                        @if ($item['children'])
                            <div x-data="{ open: {{ $item['active'] ? 'true' : 'false' }} }" class="border-b border-slate-100">
                                <button type="button" class="flex w-full items-center justify-between py-4 text-left text-lg font-semibold {{ $item['active'] ? 'text-brand' : 'text-ink' }}" @click="open = !open" :aria-expanded="open">
                                    {{ $item['label'] }} <span class="transition" :class="open && 'rotate-180'"><x-site.icon name="chevron-down" class="size-5" /></span>
                                </button>
                                <div x-show="open" x-transition class="space-y-1 pb-4 pl-3">
                                    @if ($item['url'] !== '#')
                                        <a href="{{ $item['url'] }}" class="block py-2 text-sm font-semibold uppercase tracking-wider text-brand2">{{ $item['label'] }} →</a>
                                    @endif
                                    @foreach ($item['children'] as $child)
                                        <a href="{{ $child['url'] }}" class="block py-2 {{ $child['active'] ? 'font-semibold text-brand' : 'text-slate-700' }}">{{ $child['label'] }}</a>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <a href="{{ $item['url'] }}" class="block border-b border-slate-100 py-4 text-lg font-semibold {{ $item['active'] ? 'text-brand' : 'text-ink' }}">{{ $item['label'] }}</a>
                        @endif
                    @endforeach
                </nav>
                <div class="space-y-4 border-t p-5">
                    <div class="flex gap-2">
                        @foreach ($alternates as $code => $href)
                            <a href="{{ $href }}" hreflang="{{ $code }}" class="flex-1 rounded border py-2 text-center text-sm font-semibold {{ $code === app()->getLocale() ? 'border-brand bg-brand text-white' : 'border-slate-200 text-slate-600' }}">{{ locales()[$code] }}</a>
                        @endforeach
                    </div>
                    @if ($ctaLabel)
                        <a href="{{ $ctaLink }}" class="btn btn-primary w-full">{{ $ctaLabel }}</a>
                    @endif
                </div>
            </div>
        </div>
    </header>

    @include('site.partials.news-ticker')

    <main id="main" class="flex-1">
        @yield('content')
    </main>

    {{-- Pied de page --}}
    <footer class="tone-dark relative overflow-hidden bg-ink text-white/75">
        <div class="elc-pattern absolute inset-0 opacity-50"></div>
        <div class="container-elc relative">
            @if (setting('footer.newsletter', true))
                <div class="flex flex-col gap-6 border-b border-white/10 py-12 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="font-heading text-2xl font-bold text-white">{{ tr(setting('footer.newsletter_title')) ?: __('site.newsletter.title') }}</p>
                        <p class="mt-1 text-white/70">{{ __('site.newsletter.text') }}</p>
                    </div>
                    <div class="w-full max-w-md">@include('site.partials.newsletter-form', ['dark' => true])</div>
                </div>
            @endif

            <div class="grid gap-12 py-14 md:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1.2fr]">
                <div>
                    @if ($logoLight)
                        <img src="{{ $logoLight }}" alt="{{ $siteName }}" class="h-14 w-auto">
                    @else
                        <span class="inline-block rounded bg-white p-2.5"><img src="{{ $logo }}" alt="{{ $siteName }}" class="h-11 w-auto"></span>
                    @endif
                    @if (tr(setting('footer.about')))
                        <p class="mt-6 max-w-sm leading-relaxed text-white/70">{{ tr(setting('footer.about')) }}</p>
                    @endif
                    @include('site.partials.socials', ['light' => true, 'class' => 'mt-6'])
                </div>

                @if ($footerLinks)
                    <div class="lg:col-span-2 grid gap-10 sm:grid-cols-2">
                        @foreach ($footerLinks as $column)
                            <div>
                                <p class="mb-5 text-sm font-semibold uppercase tracking-[0.15em] text-white">{{ $column['label'] }}</p>
                                <ul class="space-y-3 text-[15px]">
                                    @forelse ($column['children'] as $link)
                                        <li><a href="{{ $link['url'] }}" @if ($link['new_tab']) target="_blank" rel="noopener" @endif class="transition hover:text-white">{{ $link['label'] }}</a></li>
                                    @empty
                                        <li><a href="{{ $column['url'] }}" class="transition hover:text-white">{{ $column['label'] }}</a></li>
                                    @endforelse
                                </ul>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div>
                    <p class="mb-5 text-sm font-semibold uppercase tracking-[0.15em] text-white">{{ __('site.footer.contact') }}</p>
                    <ul class="space-y-4 text-[15px]">
                        @if (tr(setting('contact.address')))
                            <li class="flex gap-3"><x-site.icon name="map-pin" class="mt-1 size-4 shrink-0 text-accent2" /><span>{!! nl2br(e(tr(setting('contact.address')))) !!}</span></li>
                        @endif
                        @if ($email)
                            <li class="flex gap-3"><x-site.icon name="mail" class="mt-1 size-4 shrink-0 text-accent2" /><a href="mailto:{{ $email }}" class="break-all hover:text-white">{{ $email }}</a></li>
                        @endif
                        @foreach ($phones as $phone)
                            <li class="flex gap-3"><x-site.icon name="phone" class="mt-1 size-4 shrink-0 text-accent2" /><a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}" class="hover:text-white">{{ $phone }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="flex flex-col gap-4 border-t border-white/10 py-6 text-sm text-white/55 md:flex-row md:items-center md:justify-between">
                <p>{{ str_replace('{year}', date('Y'), tr(setting('footer.copyright')) ?: '© {year} '.$siteName) }} {{ __('site.footer.rights') }}</p>
                @if ($legalLinks)
                    <ul class="flex flex-wrap gap-x-6 gap-y-2">
                        @foreach ($legalLinks as $link)
                            <li><a href="{{ $link['url'] }}" class="hover:text-white">{{ $link['label'] }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </footer>
</body>
</html>
