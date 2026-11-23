@php $items = collect($d['items'] ?? [])->filter(fn ($i) => tr($i['question'] ?? null))->values(); @endphp
<div class="container-elc">
    <div class="grid gap-12 lg:grid-cols-[1fr_1.6fr]">
        <x-site.heading :eyebrow="$d['eyebrow'] ?? null" :title="$d['title'] ?? null" :intro="$d['intro'] ?? null" class="!mb-0" />
        <div class="divide-y border-y {{ $dark ? 'divide-white/15 border-white/15' : 'divide-slate-200 border-slate-200' }}">
            @foreach ($items as $item)
                <details class="group reveal py-1" @if ($loop->first) open @endif>
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-6 py-5 font-heading text-lg font-bold [&::-webkit-details-marker]:hidden {{ $dark ? 'text-white' : 'text-ink' }}">
                        {{ tr($item['question']) }}
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-full border transition {{ $dark ? 'border-white/30' : 'border-slate-300' }} group-open:rotate-45 group-open:bg-brand group-open:text-white">
                            <x-site.icon name="plus" class="size-4" />
                        </span>
                    </summary>
                    <div class="elc-prose pb-6 pr-12">{!! \App\Cms\Html::render(tr($item['answer'] ?? null)) !!}</div>
                </details>
            @endforeach
        </div>
    </div>
</div>
