<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Configuración — Usuarios</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded p-6 space-y-4">

                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('settings.index') }}" class="underline">← Configuración</a>
                    @can('crear-usuarios')
                        <a href="{{ route('settings.users.create') }}" class="text-blue-700 underline">+ Nuevo usuario</a>
                    @endcan
                </div>

                <form method="GET" action="{{ route('settings.users.index') }}" class="flex flex-wrap gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre o correo..." class="border-gray-300 rounded-md">
                    <select name="role" class="border-gray-300 rounded-md">
                        <option value="">Todos los roles</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" @selected(request('role') === $role)>{{ $role }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="border-gray-300 rounded-md">
                        <option value="">Todos los estados</option>
                        <option value="active" @selected(request('status') === 'active')>Activos</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactivos</option>
                    </select>
                    <x-primary-button>Filtrar</x-primary-button>
                </form>

                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b text-left">
                            <th class="p-2">Nombre</th>
                            <th class="p-2">Correo</th>
                            <th class="p-2">Roles</th>
                            <th class="p-2">Permisos directos</th>
                            <th class="p-2">Estado</th>
                            <th class="p-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr class="border-b">
                                <td class="p-2">{{ $user->name }}</td>
                                <td class="p-2">{{ $user->email }}</td>
                                <td class="p-2">
                                    @forelse ($user->roles as $role)
                                        <span class="inline-block rounded-full bg-blue-50 px-2 py-0.5 text-xs text-blue-800">{{ $role->name }}</span>
                                    @empty
                                        <span class="text-xs text-gray-400">sin rol</span>
                                    @endforelse
                                </td>
                                <td class="p-2">{{ $user->permissions_count }}</td>
                                <td class="p-2">
                                    <x-status-badge :active="$user->active" />
                                </td>
                                <td class="p-2 space-x-2">
                                    @can('editar-usuarios')
                                        <a href="{{ route('settings.users.edit', $user) }}" class="text-blue-700 underline">Editar</a>
                                        @unless ($user->is(auth()->user()))
                                            <form method="POST" action="{{ route('settings.users.toggle', $user) }}" class="inline">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="{{ $user->active ? 'text-red-700' : 'text-green-700' }} underline">
                                                    {{ $user->active ? 'Desactivar' : 'Activar' }}
                                                </button>
                                            </form>
                                        @endunless
                                    @endcan
                                    <a href="{{ route('settings.permissions.index', ['user' => $user->id]) }}" class="text-gray-700 underline">Ver permisos</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-4 text-center text-gray-500">No hay usuarios para mostrar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
