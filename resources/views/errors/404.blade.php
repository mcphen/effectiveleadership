@php
    // La page 404 peut s'afficher hors des routes localisées : on devine la langue depuis l'URL.
    $segment = request()->segment(1);
    if (array_key_exists((string) $segment, locales())) {
        app()->setLocale($segment);
    }
@endphp
@extends('layouts.site', ['meta' => ['title' => __('site.errors.404_title'), 'noindex' => true]])

@section('content')
    <section class="relative overflow-hidden bg-white py-24 md:py-36">
        <div class="elc-pattern-dark absolute inset-0 opacity-60"></div>
        <div class="container-elc relative text-center">
            <p class="font-heading text-8xl font-bold text-brand/15 md:text-9xl">404</p>
            <h1 class="elc-title mt-4">{{ __('site.errors.404_title') }}</h1>
            <p class="elc-lead mx-auto mt-4 max-w-xl">{{ __('site.errors.404_text') }}</p>
            <a href="{{ localized_url() }}" class="btn btn-primary mt-10">{{ __('site.errors.back_home') }} <x-site.icon name="arrow-right" /></a>
        </div>
    </section>
@endsection
