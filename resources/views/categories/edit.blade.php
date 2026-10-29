<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Editar categoría</h2>
    </x-slot>

    <x-form-card :action="route('categories.update', $category)" method="PUT" :cancel="route('categories.index')" submit="Guardar cambios">
        @include('categories.partials.form')
    </x-form-card>
</x-app-layout>
