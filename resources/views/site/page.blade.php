@extends('layouts.site')

@php
    $firstType = collect($blocks)->first(fn ($b) => empty($b['hidden']))['type'] ?? null;
    $hasHeader = in_array($firstType, ['hero', 'page_header'], true);
@endphp

@section('content')
    @unless ($hasHeader || $entity->is_home)
        @include('site.partials.header', ['title' => $entity->title])
    @endunless

    @include('blocks._render', ['blocks' => $blocks, 'context' => $entity])

@endsection
