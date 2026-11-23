@props(['name', 'stroke' => 1.75])
{!! \App\Cms\Icons::svg($name, $attributes->get('class', 'size-5'), $stroke) !!}
