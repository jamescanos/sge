{{--
    Enlace del menú lateral. El ícono es opcional:
    <x-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')" icon="cube">Productos</x-nav-link>
--}}
@props(['active' => false, 'icon' => 'dot'])

@php
    $classes = $active
        ? 'group flex items-center gap-3 rounded-lg bg-brand px-3 py-2 text-sm font-semibold text-white shadow-sm shadow-black/20'
        : 'group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white';
    $iconClasses = $active ? 'h-5 w-5 shrink-0 text-white' : 'h-5 w-5 shrink-0 text-slate-400 group-hover:text-white';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }} @if ($active) aria-current="page" @endif>
    <x-icon :name="$icon" :class="$iconClasses" />
    <span class="truncate">{{ $slot }}</span>
</a>
