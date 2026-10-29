<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Clientes {{ $showTrashed ? '(papelera)' : '' }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded p-6 space-y-4">

                {{-- Los mensajes success/error ya los muestra el layout con <x-flash-messages /> --}}

                <div class="flex flex-wrap gap-4">
                    @can('crear-clientes')
                        <a href="{{ route('clients.create') }}" class="text-blue-700 underline">+ Nuevo cliente</a>
                    @endcan
                    @can('eliminar-clientes')
                        @if ($showTrashed)
                            <a href="{{ route('clients.index') }}" class="text-blue-700 underline">Ver clientes</a>
                        @else
                            <a href="{{ route('clients.index', ['trashed' => 1]) }}" class="text-blue-700 underline">
                                Ver papelera ({{ $trashedCount }})
                            </a>
                        @endif
                    @endcan
                </div>

                <form method="GET" action="{{ route('clients.index') }}" class="flex flex-wrap gap-2">
                    @if ($showTrashed)
                        <input type="hidden" name="trashed" value="1">
                    @endif
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre, doc o email..." class="border-gray-300 rounded-md">
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
                            <th class="p-2">Documento</th>
                            <th class="p-2">Nombre</th>
                            <th class="p-2">Email</th>
                            <th class="p-2">Teléfono</th>
                            <th class="p-2">Ventas</th>
                            <th class="p-2">Estado</th>
                            <th class="p-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($clients as $client)
                            <tr class="border-b">
                                <td class="p-2">{{ $client->document_type }} {{ $client->document_number }}</td>
                                <td class="p-2">{{ $client->name }}</td>
                                <td class="p-2">{{ $client->email }}</td>
                                <td class="p-2">{{ $client->phone }}</td>
                                <td class="p-2">{{ $client->sales_count }}</td>
                                <td class="p-2">{{ $client->active ? 'Activo' : 'Inactivo' }}</td>
                                <td class="p-2 space-x-2">
                                    @if ($client->trashed())
                                        <form method="POST" action="{{ route('clients.restore', $client) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="text-green-700 underline">Restaurar</button>
                                        </form>
                                    @else
                                        @can('editar-clientes')
                                            <a href="{{ route('clients.edit', $client) }}" class="text-blue-700 underline">Editar</a>
                                        @endcan
                                        @can('eliminar-clientes')
                                            <form method="POST" action="{{ route('clients.destroy', $client) }}" class="inline"
                                                  onsubmit="return confirm('¿Enviar este cliente a la papelera?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-700 underline">Eliminar</button>
                                            </form>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-4 text-center text-gray-500">No hay clientes para mostrar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $clients->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
