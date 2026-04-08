@props([
    'name' => 'Funflow',
])

@php
    $sourceName = trim((string) $name);
    $iconLetter = strtoupper(substr($sourceName !== '' ? $sourceName : 'Funflow', 0, 1));
@endphp

<span {{ $attributes->merge(['class' => 'funflow-logo-icon']) }} aria-hidden="true">{{ $iconLetter }}</span>
