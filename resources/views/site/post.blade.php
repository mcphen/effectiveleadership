@extends('layouts.site')

@php
    /** @var \App\Models\Post $entity */
    $more = \App\Models\Post::query()->published()->ordered()->whereKeyNot($entity->id)->limit(3)->get();
    $shareUrl = urlencode($entity->url());
    $shareTitle = urlencode($entity->title);
@endphp

@section('content')
    @include('site.partials.header', ['title' => $entity->title, 'eyebrow' => $entity->typeLabel(), 'subtitle' => $entity->excerpt])

    <div class="bg-white">
        <div class="container-elc flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 py-5 text-sm text-slate-500">
            <p class="flex flex-wrap items-center gap-x-5 gap-y-1">
                @if ($entity->published_at)
                    <span class="inline-flex items-center gap-2"><x-site.icon name="calendar" class="size-4" />{{ __('site.published_on', ['date' => $entity->published_at->translatedFormat('j F Y')]) }}</span>
                @endif
                @if ($entity->author)
                    <span>{{ __('site.by', ['author' => $entity->author]) }}</span>
                @endif
            </p>
            <p class="flex items-center gap-2">
                <span>{{ __('site.share') }}</span>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" target="_blank" rel="noopener" class="flex size-9 items-center justify-center rounded-full border border-slate-200 hover:border-brand hover:text-brand" aria-label="LinkedIn"><x-site.icon name="linkedin" class="size-4" /></a>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener" class="flex size-9 items-center justify-center rounded-full border border-slate-200 hover:border-brand hover:text-brand" aria-label="Facebook"><x-site.icon name="facebook" class="size-4" /></a>
                <a href="mailto:?subject={{ $shareTitle }}&body={{ $shareUrl }}" class="flex size-9 items-center justify-center rounded-full border border-slate-200 hover:border-brand hover:text-brand" aria-label="E-mail"><x-site.icon name="mail" class="size-4" /></a>
            </p>
        </div>
    </div>

    @if ($entity->cover)
        <div class="bg-white pt-12">
            <div class="container-elc">
                <x-site.img :src="$entity->cover" :alt="$entity->title" :eager="true" class="mx-auto aspect-[21/9] w-full max-w-5xl object-cover shadow-lg" style="border-radius: calc(var(--radius) * 2)" />
            </div>
        </div>
    @endif

    @include('blocks._render', ['blocks' => $blocks, 'context' => $entity])

    @if ($entity->attachment)
        <div class="bg-white pb-16">
            <div class="container-elc">
                <a href="{{ media_url($entity->attachment) }}" target="_blank" rel="noopener" download class="group mx-auto flex max-w-3xl items-center gap-5 border border-slate-200 bg-slate-50 p-6 transition hover:border-brand hover:bg-white" style="border-radius: calc(var(--radius) * 2)">
                    <span class="flex size-14 shrink-0 items-center justify-center rounded-full bg-brand text-white"><x-site.icon name="download" class="size-6" /></span>
                    <span class="flex-1">
                        <span class="block font-heading text-lg font-bold text-ink">{{ __('site.download_pdf') }}</span>
                        <span class="block text-sm text-slate-500">{{ basename($entity->attachment) }}</span>
                    </span>
                    <x-site.icon name="arrow-right" class="size-5 text-brand transition group-hover:translate-x-1" />
                </a>
            </div>
        </div>
    @endif

    @if ($more->isNotEmpty())
        <section class="border-t border-slate-200 bg-slate-50 py-16 md:py-20">
            <div class="container-elc">
                <x-site.heading :eyebrow="'Insights'" :title="app()->getLocale() === 'fr' ? 'À lire aussi' : 'Read also'" />
                <div class="grid gap-8 md:grid-cols-3">
                    @foreach ($more as $i => $post)
                        @include('site.partials.card-post', ['post' => $post, 'delay' => $i * 80])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
