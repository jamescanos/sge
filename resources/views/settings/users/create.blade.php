<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nuevo usuario</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('settings.users.store') }}" class="bg-white shadow rounded p-6 space-y-4">
                @csrf
                @include('settings.users.partials.form')
                <div class="flex items-center gap-4 pt-4 border-t">
                    <x-primary-button>Guardar</x-primary-button>
                    <a href="{{ route('settings.users.index') }}" class="underline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
