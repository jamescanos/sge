<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Configuración — Roles</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded p-6 space-y-4">

                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('settings.index') }}" class="underline">← Configuración</a>
                    @can('crear-roles')
                        <a href="{{ route('settings.roles.create') }}" class="text-blue-700 underline">+ Nuevo rol</a>
                    @endcan
                </div>

                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b text-left">
                            <th class="p-2">Rol</th>
                            <th class="p-2">Usuarios</th>
                            <th class="p-2">Permisos</th>
                            <th class="p-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $role)
                            <tr class="border-b">
                                <td class="p-2 font-medium">
                                    {{ $role->name }}
                                    @if ($role->name === $superRole)
                                        <span class="ml-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-800">todos los permisos</span>
                                    @endif
                                </td>
                                <td class="p-2">{{ $role->users_count }}</td>
                                <td class="p-2">{{ $role->permissions_count }}</td>
                                <td class="p-2 space-x-2">
                                    @can('editar-roles')
                                        <a href="{{ route('settings.roles.edit', $role) }}" class="text-blue-700 underline">
                                            {{ $role->name === $superRole ? 'Ver' : 'Editar' }}
                                        </a>
                                    @endcan
                                    @can('eliminar-roles')
                                        @if ($role->name !== $superRole)
                                            <form method="POST" action="{{ route('settings.roles.destroy', $role) }}" class="inline"
                                                  onsubmit="return confirm('¿Eliminar el rol {{ $role->name }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-700 underline">Eliminar</button>
                                            </form>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
