@php
    use App\Models\Solution;
    use App\Models\SolutionCategory;

    $source = $d['source'] ?? 'poles';
    $categoryId = $d['category_id'] ?? null;
    $cols = ['2' => 'md:grid-cols-2', '3' => 'md:grid-cols-2 lg:grid-cols-3', '4' => 'sm:grid-cols-2 lg:grid-cols-4'][$d['columns'] ?? '3'] ?? 'md:grid-cols-3';
    $linkLabel = tr($d['link_label'] ?? null) ?: __('site.read_more');

    $items = match ($source) {
        'categories' => SolutionCategory::query()->published()->where('parent_id', $categoryId)->ordered()->get(),
        'solutions' => Solution::query()->published()->with('category')
            ->whereIn('solution_category_id', SolutionCategory::query()->where('id', $categoryId)->orWhere('parent_id', $categoryId)->pluck('id'))
            ->ordered()->get(),
        'featured' => Solution::query()->published()->with('category')->where('is_featured', true)->ordered()->get(),
        default => SolutionCategory::query()->published()->whereNull('parent_id')->with(['children' => fn ($q) => $q->published()])->ordered()->get(),
    };
@endphp
<div class="container-elc">
    <x-site.heading :eyebrow="$d['eyebrow'] ?? null" :title="$d['title'] ?? null" :intro="$d['intro'] ?? null" />

    @if ($source === 'poles')
        {{-- Grandes cartes des pôles avec la liste de leurs catégories --}}
        <div class="grid gap-8 lg:grid-cols-2">
            @foreach ($items as $i => $pole)
                <article class="group reveal relative isolate flex min-h-[26rem] flex-col justify-end overflow-hidden bg-ink p-8 text-white md:p-10" style="border-radius: calc(var(--radius) * 3); transition-delay: {{ $i * 100 }}ms">
                    @if ($pole->image)
                        <x-site.img :src="$pole->image" class="absolute inset-0 -z-20 h-full w-full object-cover transition duration-700 group-hover:scale-105" />
                        <div class="absolute inset-0 -z-10 bg-gradient-to-t from-ink via-ink/80 to-ink/30"></div>
                    @else
                        <div class="elc-pattern absolute inset-0 -z-10"></div>
                        <div class="absolute -right-24 -top-24 -z-10 size-80 rounded-full {{ $i % 2 ? 'bg-brand2/40' : 'bg-brand/60' }} blur-3xl"></div>
                    @endif
                    <p class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-accent2">0{{ $i + 1 }}</p>
                    <h3 class="font-heading text-3xl font-bold text-white md:text-4xl">
                        <a href="{{ $pole->url() }}" class="after:absolute after:inset-0">{{ $pole->title }}</a>
                    </h3>
                    @if ($pole->summary)
                        <p class="mt-4 max-w-lg text-white/80">{{ $pole->summary }}</p>
                    @endif
                    @if ($pole->children->isNotEmpty())
                        <ul class="relative z-10 mt-6 flex flex-wrap gap-2">
                            @foreach ($pole->children as $child)
                                <li><a href="{{ $child->url() }}" class="inline-block rounded-full border border-white/25 px-3 py-1 text-sm text-white/90 backdrop-blur transition hover:border-white hover:bg-white hover:text-ink">{{ $child->title }}</a></li>
                            @endforeach
                        </ul>
                    @endif
                    <span class="mt-8 inline-flex items-center gap-2 font-semibold text-white">{{ $linkLabel }} <x-site.icon name="arrow-right" class="size-4 transition group-hover:translate-x-1" /></span>
                </article>
            @endforeach
        </div>
    @else
        <div class="grid gap-6 {{ $cols }}">
            @foreach ($items as $i => $item)
                @include('site.partials.card-entry', ['card' => [
                    'title' => $item->title,
                    'href' => $item->url(),
                    'image' => $item->image,
                    'meta' => $item instanceof Solution ? $item->category?->title : null,
                    'summary' => $item->summary,
                    'linkLabel' => $linkLabel,
                    'delay' => ($i % 3) * 80,
                ]])
            @endforeach
        </div>
    @endif
</div>
