<x-mail::message>
# {{ $title }}

@foreach ($lines as $label => $value)
@if (filled($value))
**{{ $label }} :** {!! nl2br(e($value)) !!}

@endif
@endforeach

<x-mail::button :url="$adminUrl">
Voir dans l'administration
</x-mail::button>

Répondez directement à cet e-mail pour écrire à l'expéditeur.
</x-mail::message>
