@php $jobs = \App\Models\JobOffer::query()->open()->ordered()->get(); @endphp
<div class="container-elc">
    <x-site.heading :eyebrow="$d['eyebrow'] ?? null" :title="$d['title'] ?? null" :intro="$d['intro'] ?? null" />

    @if ($jobs->isNotEmpty())
        <ul class="space-y-4">
            @foreach ($jobs as $job)
                <li class="group reveal relative flex flex-col gap-4 border border-slate-200 bg-white p-6 transition hover:border-brand/40 hover:shadow-lg md:flex-row md:items-center md:justify-between md:p-8" style="border-radius: calc(var(--radius) * 2)">
                    <div>
                        <h3 class="font-heading text-xl font-bold text-ink"><a href="{{ $job->url() }}" class="after:absolute after:inset-0">{{ $job->title }}</a></h3>
                        <p class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-sm text-slate-500">
                            @if ($job->location)<span class="inline-flex items-center gap-1.5"><x-site.icon name="map-pin" class="size-4" />{{ $job->location }}</span>@endif
                            @if ($job->contract_type)<span class="inline-flex items-center gap-1.5"><x-site.icon name="briefcase" class="size-4" />{{ $job->contract_type }}</span>@endif
                            @if ($job->closes_at)<span class="inline-flex items-center gap-1.5"><x-site.icon name="clock" class="size-4" />{{ __('site.deadline') }} : {{ $job->closes_at->translatedFormat('j F Y') }}</span>@endif
                        </p>
                    </div>
                    <span class="btn btn-outline shrink-0">{{ __('site.apply') }} <x-site.icon name="arrow-right" /></span>
                </li>
            @endforeach
        </ul>
    @elseif (tr($d['empty_text'] ?? null))
        <p class="reveal rounded-lg border border-dashed border-slate-300 bg-white px-6 py-10 text-center text-slate-500">{{ tr($d['empty_text']) }}</p>
    @endif

    @if (! empty($d['spontaneous']))
        <div class="reveal mt-14 grid gap-10 border-t border-slate-200 pt-14 lg:grid-cols-[1fr_1.4fr]">
            <div>
                <h3 class="font-heading text-2xl font-bold">{{ __('site.spontaneous') }}</h3>
                <p class="elc-muted mt-3">{{ __('site.spontaneous_text') }}</p>
            </div>
            @include('site.partials.apply-form', ['job' => null])
        </div>
    @endif
</div>
