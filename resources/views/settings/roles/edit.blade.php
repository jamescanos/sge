<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Rol: {{ $role->name }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('settings.roles.update', $role) }}" class="bg-white shadow rounded p-6 space-y-4">
                @csrf
                @method('PUT')
                @include('settings.roles.partials.form')
                <div class="flex items-center gap-4 pt-4 border-t">
                    @unless ($isSuperRole)
                        <x-primary-button>Actualizar</x-primary-button>
                    @endunless
                    <a href="{{ route('settings.roles.index') }}" class="underline">Volver</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
