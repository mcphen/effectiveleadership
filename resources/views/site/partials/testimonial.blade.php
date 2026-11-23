<x-site.icon name="quote" class="size-9 text-accent2" />
<blockquote class="mt-5 font-heading {{ ! empty($small) ? 'text-lg' : 'text-xl md:text-2xl' }} italic leading-relaxed text-ink">
    « {{ $t->quote }} »
</blockquote>
<figcaption class="mt-7 flex items-center gap-4">
    @if ($t->photo)
        <x-site.img :src="$t->photo" :alt="$t->author" class="size-14 rounded-full object-cover ring-4 ring-white" />
    @else
        <span class="flex size-14 items-center justify-center rounded-full bg-brand font-heading text-lg font-bold text-white">{{ mb_substr($t->author, 0, 1) }}</span>
    @endif
    <span class="text-left">
        <span class="block font-semibold text-ink">{{ $t->author }}</span>
        <span class="elc-muted block text-sm">{{ collect([$t->role, $t->company])->filter()->implode(', ') }}</span>
    </span>
</figcaption>
