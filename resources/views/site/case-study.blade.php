@extends('layouts.site')

@php
    /** @var \App\Models\CaseStudy $entity */
    $facts = collect([
        __('site.client') => $entity->client,
        __('site.sector') => $entity->sector,
        __('site.country') => $entity->country,
        __('site.year') => $entity->year,
    ])->filter();
@endphp

@section('content')
    @include('site.partials.header', ['title' => $entity->title, 'subtitle' => $entity->summary, 'image' => $entity->image])

    @if ($facts->isNotEmpty())
        <div class="border-b border-slate-200 bg-white">
            <dl class="container-elc grid grid-cols-2 gap-6 py-8 md:grid-cols-4">
                @foreach ($facts as $label => $value)
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-[0.15em] text-brand2">{{ $label }}</dt>
                        <dd class="mt-1 font-heading text-lg font-bold text-ink">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    @endif

    @include('blocks._render', ['blocks' => $blocks, 'context' => $entity])
    @include('site.partials.contact-cta')
@endsection
