@extends('layouts.site')

@php
    /** @var \App\Models\Solution $entity */
    $category = $entity->category;
    $trail = collect();
    if ($category?->parent) {
        $trail[$category->parent->title] = $category->parent->url();
    }
    if ($category) {
        $trail[$category->title] = $category->url();
    }
    $related = $category ? $category->solutions()->published()->whereKeyNot($entity->id)->get() : collect();
@endphp

@section('content')
    @include('site.partials.header', [
        'title' => $entity->title,
        'eyebrow' => $category?->title,
        'subtitle' => $entity->subtitle ?: $entity->summary,
        'signature' => $entity->signature,
        'image' => $entity->image,
        'trail' => $trail->all(),
    ])

    @include('blocks._render', ['blocks' => $blocks, 'context' => $entity])

    @if ($related->isNotEmpty())
        <section class="border-t border-slate-200 bg-slate-50 py-16 md:py-20">
            <div class="container-elc">
                <x-site.heading :eyebrow="$category->title" :title="__('site.related_offers')" />
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $i => $solution)
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
