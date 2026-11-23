@extends('layouts.site')

@php /** @var \App\Models\JobOffer $entity */ @endphp

@section('content')
    @include('site.partials.header', ['title' => $entity->title, 'subtitle' => $entity->summary])

    <div class="border-b border-slate-200 bg-white">
        <div class="container-elc flex flex-wrap items-center justify-between gap-6 py-6">
            <dl class="flex flex-wrap gap-x-10 gap-y-3 text-sm">
                @foreach ([__('site.location') => $entity->location, __('site.contract') => $entity->contract_type, __('site.deadline') => $entity->closes_at?->translatedFormat('j F Y')] as $label => $value)
                    @if ($value)
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-[0.15em] text-brand2">{{ $label }}</dt>
                            <dd class="mt-0.5 font-semibold text-ink">{{ $value }}</dd>
                        </div>
                    @endif
                @endforeach
            </dl>
            <a href="#apply-form" class="btn btn-primary">{{ __('site.apply') }} <x-site.icon name="arrow-right" /></a>
        </div>
    </div>

    @include('blocks._render', ['blocks' => $blocks, 'context' => $entity])

    <section class="bg-slate-50 py-16 md:py-20">
        <div class="container-elc grid gap-10 lg:grid-cols-[1fr_1.5fr]">
            <div>
                <h2 class="elc-title !text-3xl">{{ __('site.apply_title') }}</h2>
                <p class="elc-muted mt-3">{{ $entity->title }}</p>
            </div>
            @include('site.partials.apply-form', ['job' => $entity])
        </div>
    </section>
@endsection
