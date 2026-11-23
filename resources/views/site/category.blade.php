@extends('layouts.site')

@php
    /** @var \App\Models\SolutionCategory $entity */
    $trail = $entity->parent ? [$entity->parent->title => $entity->parent->url()] : [];
    $children = $entity->isPole() ? $entity->children()->published()->with(['solutions' => fn ($q) => $q->published()])->get() : collect();
    $solutions = $entity->isPole() ? collect() : $entity->solutions()->published()->get();
@endphp

@section('content')
    @include('site.partials.header', [
        'title' => $entity->title,
        'eyebrow' => $entity->parent?->title,
        'subtitle' => $entity->summary,
        'image' => $entity->image,
        'trail' => $trail,
    ])

    @include('blocks._render', ['blocks' => $blocks, 'context' => $entity])

    @if ($children->isNotEmpty())
        {{-- Pôle : chaque catégorie avec ses offres --}}
        <section class="bg-white py-16 md:py-24">
            <div class="container-elc space-y-20">
                @foreach ($children as $category)
                    <div class="grid gap-10 lg:grid-cols-[1fr_2fr]">
                        <div class="reveal lg:sticky lg:top-32 lg:self-start">
                            <p class="elc-eyebrow">0{{ $loop->iteration }}</p>
                            <h2 class="elc-title !text-3xl"><a href="{{ $category->url() }}" class="hover:text-brand">{{ $category->title }}</a></h2>
                            @if ($category->summary)
                                <p class="elc-muted mt-4 leading-relaxed">{{ $category->summary }}</p>
                            @endif
                            <a href="{{ $category->url() }}" class="btn btn-link mt-5 !p-0">{{ __('site.discover') }} <x-site.icon name="arrow-right" /></a>
                        </div>
                        <div class="grid gap-6 sm:grid-cols-2">
                            @foreach ($category->solutions as $i => $solution)
                                @include('site.partials.card-entry', ['card' => [
                                    'title' => $solution->title,
                                    'href' => $solution->url(),
                                    'image' => $solution->image,
                                    'summary' => $solution->summary,
                                    'delay' => ($i % 2) * 80,
                                ]])
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @elseif ($solutions->isNotEmpty())
        <section class="bg-slate-50 py-16 md:py-24">
            <div class="container-elc">
                <x-site.heading :eyebrow="$entity->title" :title="__('site.our_offers')" />
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($solutions as $i => $solution)
                        @include('site.partials.card-entry', ['card' => [
                            'title' => $solution->title,
                            'href' => $solution->url(),
                            'image' => $solution->image,
                            'summary' => $solution->summary,
                            'delay' => ($i % 3) * 80,
                        ]])
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @include('site.partials.contact-cta')
@endsection
