<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ventas</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded p-6 space-y-4">

                <div class="flex flex-wrap justify-between gap-4 items-center">
                    @can('crear-ventas')
                        <a href="{{ route('sales.create') }}" class="text-blue-700 underline">+ Nueva venta</a>
                    @endcan
                    <div class="text-sm text-gray-700">
                        <strong>Total del mes:</strong>
                        ${{ number_format((float) $totalMonth, 2) }}
                    </div>
                </div>

                <form method="GET" action="{{ route('sales.index') }}" class="flex flex-wrap gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por factura o cliente..." class="border-gray-300 rounded-md">
                    <input type="date" name="from" value="{{ request('from') }}" class="border-gray-300 rounded-md">
                    <input type="date" name="to" value="{{ request('to') }}" class="border-gray-300 rounded-md">
                    <x-primary-button>Filtrar</x-primary-button>
                </form>

                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b text-left">
                            <th class="p-2">Factura</th>
                            <th class="p-2">Fecha</th>
                            <th class="p-2">Cliente</th>
                            <th class="p-2">Vendedor</th>
                            <th class="p-2">Total</th>
                            <th class="p-2">Estado</th>
                            <th class="p-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $sale)
                            <tr class="border-b">
                                <td class="p-2">{{ $sale->invoice_number }}</td>
                                <td class="p-2">{{ $sale->sale_date->format('d/m/Y') }}</td>
                                <td class="p-2">{{ $sale->client?->name }}</td>
                                <td class="p-2">{{ $sale->user?->name }}</td>
                                <td class="p-2">${{ number_format((float) $sale->total, 2) }}</td>
                                <td class="p-2">{{ ucfirst($sale->status) }}</td>
                                <td class="p-2 space-x-2">
                                    <a href="{{ route('sales.show', $sale) }}" class="text-blue-700 underline">Ver</a>
                                    <a href="{{ route('sales.pdf', $sale) }}" target="_blank" class="text-gray-700 underline">PDF</a>
                                    @can('eliminar-ventas')
                                        <form method="POST" action="{{ route('sales.destroy', $sale) }}" class="inline"
                                              onsubmit="return confirm('¿Anular esta venta? El stock será devuelto.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-700 underline">Anular</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-4 text-center text-gray-500">No hay ventas para mostrar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $sales->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
