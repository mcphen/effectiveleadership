@php
    $limit = (int) ($d['limit'] ?? 6);
    $items = \App\Models\CaseStudy::query()->published()->ordered()->when($limit > 0, fn ($q) => $q->limit($limit))->get();
@endphp
<div class="container-elc">
    <x-site.heading :eyebrow="$d['eyebrow'] ?? null" :title="$d['title'] ?? null" :intro="$d['intro'] ?? null" />
    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        @foreach ($items as $i => $case)
            @include('site.partials.card-entry', ['card' => [
                'title' => $case->title,
                'href' => $case->url(),
                'image' => $case->image,
                'meta' => collect([$case->client, $case->sector, $case->year])->filter()->implode(' · '),
                'summary' => $case->summary,
                'delay' => ($i % 3) * 80,
            ]])
        @endforeach
    </div>
    <x-site.buttons :buttons="$d['button'] ?? []" class="reveal mt-12 justify-center" />
</div>
