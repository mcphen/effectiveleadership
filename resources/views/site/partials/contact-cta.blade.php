{{-- Bandeau « Parlons de votre projet » en bas des pages d'offres --}}
@php $link = resolve_link(setting('header.cta_link')); @endphp
<section class="tone-dark relative overflow-hidden bg-brand py-16 md:py-20">
    <div class="elc-pattern absolute inset-0"></div>
    <div class="container-elc relative flex flex-col items-start justify-between gap-8 lg:flex-row lg:items-center">
        <div class="max-w-2xl">
            <h2 class="font-heading text-3xl font-bold md:text-4xl">{{ __('site.talk_to_us') }}</h2>
            <p class="elc-lead mt-3">{{ __('site.talk_to_us_text') }}</p>
        </div>
        @if ($link !== '#')
            <a href="{{ $link }}" class="btn btn-primary shrink-0">{{ tr(setting('header.cta_label')) ?: __('site.contact_us') }} <x-site.icon name="arrow-right" /></a>
        @endif
    </div>
</section>
