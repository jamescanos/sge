<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Compras</h2>
    </x-slot>

    @php
        $statusColors = [
            'pendiente' => 'bg-amber-100 text-amber-800',
            'parcial' => 'bg-blue-100 text-blue-800',
            'recibida' => 'bg-green-100 text-green-800',
            'anulada' => 'bg-gray-200 text-gray-700',
        ];
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded p-6 space-y-4">

                <div class="flex flex-wrap justify-between gap-4 items-center">
                    @can('crear-compras')
                        <a href="{{ route('purchases.create') }}" class="text-blue-700 underline">+ Nueva orden de compra</a>
                    @endcan
                    <div class="text-sm text-gray-700 space-x-4">
                        <span><strong>Compras del mes:</strong> ${{ number_format((float) $totalMonth, 2) }}</span>
                        <span><strong>Órdenes por recibir:</strong> {{ $pendingCount }}</span>
                    </div>
                </div>

                <form method="GET" action="{{ route('purchases.index') }}" class="flex flex-wrap gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por OC, factura o proveedor..." class="border-gray-300 rounded-md">
                    <select name="status" class="border-gray-300 rounded-md">
                        <option value="">Todos los estados</option>
                        @foreach (['pendiente' => 'Pendiente', 'parcial' => 'Parcial', 'recibida' => 'Recibida'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <input type="date" name="from" value="{{ request('from') }}" class="border-gray-300 rounded-md">
                    <input type="date" name="to" value="{{ request('to') }}" class="border-gray-300 rounded-md">
                    <x-primary-button>Filtrar</x-primary-button>
                </form>

                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b text-left">
                            <th class="p-2">Orden</th>
                            <th class="p-2">Fecha</th>
                            <th class="p-2">Proveedor</th>
                            <th class="p-2">Registró</th>
                            <th class="p-2">Total</th>
                            <th class="p-2">Estado</th>
                            <th class="p-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($purchases as $purchase)
                            <tr class="border-b">
                                <td class="p-2">{{ $purchase->purchase_number }}</td>
                                <td class="p-2">{{ $purchase->purchase_date->format('d/m/Y') }}</td>
                                <td class="p-2">{{ $purchase->provider?->name }}</td>
                                <td class="p-2">{{ $purchase->user?->name }}</td>
                                <td class="p-2">${{ number_format((float) $purchase->total, 2) }}</td>
                                <td class="p-2">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$purchase->status] ?? '' }}">
                                        {{ ucfirst($purchase->status) }}
                                    </span>
                                </td>
                                <td class="p-2 space-x-2">
                                    <a href="{{ route('purchases.show', $purchase) }}" class="text-blue-700 underline">Ver</a>
                                    <a href="{{ route('purchases.pdf', $purchase) }}" target="_blank" class="text-gray-700 underline">PDF</a>
                                    @can('eliminar-compras')
                                        @if ($purchase->canBeCancelled())
                                            <form method="POST" action="{{ route('purchases.destroy', $purchase) }}" class="inline"
                                                  onsubmit="return confirm('¿Anular esta orden de compra?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-700 underline">Anular</button>
                                            </form>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-4 text-center text-gray-500">No hay compras para mostrar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $purchases->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
