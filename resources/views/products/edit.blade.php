<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Editar producto</h2>
    </x-slot>

    <x-form-card :action="route('products.update', $product)" method="PUT" :cancel="route('products.index')" submit="Guardar cambios">
        @include('products.partials.form')
    </x-form-card>
</x-app-layout>
