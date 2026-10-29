<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Proveedores {{ $showTrashed ? '(papelera)' : '' }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded p-6 space-y-4">

                <div class="flex flex-wrap gap-4">
                    @can('crear-proveedores')
                        <a href="{{ route('providers.create') }}" class="text-blue-700 underline">+ Nuevo proveedor</a>
                    @endcan
                    @can('eliminar-proveedores')
                        @if ($showTrashed)
                            <a href="{{ route('providers.index') }}" class="text-blue-700 underline">Ver proveedores</a>
                        @else
                            <a href="{{ route('providers.index', ['trashed' => 1]) }}" class="text-blue-700 underline">
                                Ver papelera ({{ $trashedCount }})
                            </a>
                        @endif
                    @endcan
                </div>

                <form method="GET" action="{{ route('providers.index') }}" class="flex flex-wrap gap-2">
                    @if ($showTrashed)
                        <input type="hidden" name="trashed" value="1">
                    @endif
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por razón social, NIT o contacto..." class="border-gray-300 rounded-md">
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
                            <th class="p-2">Razón social</th>
                            <th class="p-2">Contacto</th>
                            <th class="p-2">Teléfono</th>
                            <th class="p-2">Compras</th>
                            <th class="p-2">Estado</th>
                            <th class="p-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($providers as $provider)
                            <tr class="border-b">
                                <td class="p-2">{{ $provider->document_type }} {{ $provider->document_number }}</td>
                                <td class="p-2">{{ $provider->name }}</td>
                                <td class="p-2">{{ $provider->contact_name }}</td>
                                <td class="p-2">{{ $provider->phone }}</td>
                                <td class="p-2">{{ $provider->purchases_count }}</td>
                                <td class="p-2">{{ $provider->active ? 'Activo' : 'Inactivo' }}</td>
                                <td class="p-2 space-x-2">
                                    @if ($provider->trashed())
                                        <form method="POST" action="{{ route('providers.restore', $provider) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="text-green-700 underline">Restaurar</button>
                                        </form>
                                    @else
                                        @can('editar-proveedores')
                                            <a href="{{ route('providers.edit', $provider) }}" class="text-blue-700 underline">Editar</a>
                                        @endcan
                                        @can('eliminar-proveedores')
                                            <form method="POST" action="{{ route('providers.destroy', $provider) }}" class="inline"
                                                  onsubmit="return confirm('¿Enviar este proveedor a la papelera?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-700 underline">Eliminar</button>
                                            </form>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-4 text-center text-gray-500">No hay proveedores para mostrar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $providers->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
