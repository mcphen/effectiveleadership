@php
    $url = (string) ($d['url'] ?? '');
    $embed = null;
    if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([\w-]{11})~', $url, $m)) {
        $embed = 'https://www.youtube-nocookie.com/embed/'.$m[1].'?rel=0';
    } elseif (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
        $embed = 'https://player.vimeo.com/video/'.$m[1];
    }
@endphp
<div class="container-elc">
    <x-site.heading :eyebrow="$d['eyebrow'] ?? null" :title="$d['title'] ?? null" :intro="$d['intro'] ?? null" align="center" />
    @if ($embed)
        <div class="reveal mx-auto aspect-video max-w-5xl overflow-hidden bg-ink shadow-2xl" style="border-radius: calc(var(--radius) * 2)">
            <iframe src="{{ $embed }}" title="{{ tr($d['title'] ?? null) ?: 'Vidéo' }}" class="h-full w-full" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
    @endif
</div>
