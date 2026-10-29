@props(['route', 'showTrashed' => false, 'trashedCount' => 0])

@php
    $tabClasses = 'inline-flex items-center gap-2 border-b-2 px-1 pb-3 text-sm font-medium transition';
    $activeClasses = 'border-teal-600 text-teal-800 dark:border-teal-400 dark:text-teal-300';
    $inactiveClasses = 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200';
@endphp

<nav class="flex gap-6 border-b border-gray-200 dark:border-gray-700" aria-label="Vista">
    <a href="{{ route($route) }}" @class([$tabClasses, $showTrashed ? $inactiveClasses : $activeClasses]) @if (! $showTrashed) aria-current="page" @endif>
        Registros
    </a>
    <a href="{{ route($route, ['trashed' => 1]) }}" @class([$tabClasses, $showTrashed ? $activeClasses : $inactiveClasses]) @if ($showTrashed) aria-current="page" @endif>
        Papelera
        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs tabular-nums text-gray-700 dark:bg-gray-700 dark:text-gray-200">{{ $trashedCount }}</span>
    </a>
</nav>
