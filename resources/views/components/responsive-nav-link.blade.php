{{-- Enlace de las opciones de usuario en el menú lateral (pantallas pequeñas) --}}
@props(['active' => false])

@php
    $classes = $active
        ? 'block w-full rounded-lg bg-white/10 px-3 py-2 text-start text-sm font-semibold text-white'
        : 'block w-full rounded-lg px-3 py-2 text-start text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
