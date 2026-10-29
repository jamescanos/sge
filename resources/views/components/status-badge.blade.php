@props(['active' => true, 'trashed' => false])

@if ($trashed)
    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700 ring-1 ring-inset ring-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:ring-gray-600">En papelera</span>
@elseif ($active)
    <span class="inline-flex items-center rounded-full bg-teal-50 px-2 py-0.5 text-xs font-medium text-teal-800 ring-1 ring-inset ring-teal-600/20 dark:bg-teal-950 dark:text-teal-200 dark:ring-teal-400/30">Activo</span>
@else
    <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-800 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-950 dark:text-amber-200 dark:ring-amber-400/30">Inactivo</span>
@endif
