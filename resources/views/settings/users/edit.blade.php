<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar usuario: {{ $user->name }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('settings.users.update', $user) }}" class="bg-white shadow rounded p-6 space-y-4">
                @csrf
                @method('PUT')
                @include('settings.users.partials.form')
                <div class="flex items-center gap-4 pt-4 border-t">
                    <x-primary-button>Actualizar</x-primary-button>
                    <a href="{{ route('settings.users.index') }}" class="underline">Cancelar</a>
                    <a href="{{ route('settings.permissions.index', ['user' => $user->id]) }}" class="text-gray-700 underline">Ver permisos efectivos</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
