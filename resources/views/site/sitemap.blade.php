{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach ($entries as $entry)
@foreach ($entry['urls'] as $locale => $url)
    <url>
        <loc>{{ $url }}</loc>
@if ($entry['updated'])
        <lastmod>{{ $entry['updated'] }}</lastmod>
@endif
@foreach ($entry['urls'] as $altLocale => $altUrl)
        <xhtml:link rel="alternate" hreflang="{{ $altLocale }}" href="{{ $altUrl }}" />
@endforeach
    </url>
@endforeach
@endforeach
</urlset>
