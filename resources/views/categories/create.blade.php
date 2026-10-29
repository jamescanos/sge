<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Nueva categoría</h2>
    </x-slot>

    <x-form-card :action="route('categories.store')" :cancel="route('categories.index')" submit="Guardar categoría">
        @include('categories.partials.form')
    </x-form-card>
</x-app-layout>
