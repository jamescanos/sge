<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kardex de inventario</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded p-6 space-y-4">

                <form method="GET" action="{{ route('inventory.kardex') }}" class="flex flex-wrap items-end gap-2">
                    <div>
                        <x-input-label for="product" value="Producto" />
                        <select id="product" name="product" class="mt-1 border-gray-300 rounded-md" required>
                            <option value="">Seleccione un producto</option>
                            @foreach ($products as $p)
                                <option value="{{ $p->id }}" @selected($product?->id === $p->id)>
                                    {{ $p->name }} (stock: {{ $p->stock }}){{ $p->trashed() ? ' — en papelera' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="concept" value="Concepto" />
                        <select id="concept" name="concept" class="mt-1 border-gray-300 rounded-md">
                            <option value="">Todos</option>
                            @foreach ($concepts as $value => $label)
                                <option value="{{ $value }}" @selected(request('concept') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="from" value="Desde" />
                        <input id="from" type="date" name="from" value="{{ request('from') }}" class="mt-1 border-gray-300 rounded-md">
                    </div>
                    <div>
                        <x-input-label for="to" value="Hasta" />
                        <input id="to" type="date" name="to" value="{{ request('to') }}" class="mt-1 border-gray-300 rounded-md">
                    </div>
                    <x-primary-button>Consultar</x-primary-button>
                </form>

                @if ($product)
                    <div class="flex flex-wrap items-center justify-between gap-4 border-t pt-4">
                        <div>
                            <p class="text-lg font-semibold">{{ $product->name }}</p>
                            <p class="text-sm text-gray-600">
                                Stock en el sistema: <strong>{{ $product->stock }}</strong> ·
                                Entradas: <strong class="text-green-700">{{ $totals['entradas'] }}</strong> ·
                                Salidas: <strong class="text-red-700">{{ $totals['salidas'] }}</strong>
                            </p>
                            @if ($totals['cuadra'])
                                <p class="text-sm text-green-700">✓ El saldo del kardex coincide con el stock.</p>
                            @else
                                <p class="text-sm text-red-700 font-semibold">⚠ El saldo del kardex NO coincide con el stock. Revise si hubo cambios fuera del sistema.</p>
                            @endif
                        </div>
                        <div class="space-x-3">
                            <a href="{{ route('inventory.pdf', ['product' => $product->id] + request()->only('from', 'to', 'concept')) }}" target="_blank" class="text-blue-700 underline">Descargar PDF</a>
                            @can('ajustar-inventario')
                                @unless ($product->trashed())
                                    <a href="{{ route('inventory.adjust', $product) }}" class="text-blue-700 underline">Ajustar inventario</a>
                                @endunless
                            @endcan
                        </div>
                    </div>

                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b text-left">
                                <th class="p-2">Fecha</th>
                                <th class="p-2">Concepto</th>
                                <th class="p-2">Documento</th>
                                <th class="p-2 text-right">Entrada</th>
                                <th class="p-2 text-right">Salida</th>
                                <th class="p-2 text-right">Saldo</th>
                                <th class="p-2 text-right">Valor unit.</th>
                                <th class="p-2">Usuario</th>
                                <th class="p-2">Notas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($movements as $m)
                                <tr class="border-b">
                                    <td class="p-2">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="p-2">{{ $m->conceptLabel() }}</td>
                                    <td class="p-2">{{ $m->referenceLabel() ?? '—' }}</td>
                                    <td class="p-2 text-right text-green-700">{{ $m->type === 'entrada' ? $m->quantity : '' }}</td>
                                    <td class="p-2 text-right text-red-700">{{ $m->type === 'salida' ? $m->quantity : '' }}</td>
                                    <td class="p-2 text-right font-semibold">{{ $m->balance }}</td>
                                    <td class="p-2 text-right">{{ $m->unit_cost !== null ? '$'.number_format((float) $m->unit_cost, 2) : '' }}</td>
                                    <td class="p-2">{{ $m->user?->name ?? 'Sistema' }}</td>
                                    <td class="p-2 text-xs text-gray-600">{{ $m->notes }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="p-4 text-center text-gray-500">No hay movimientos para los filtros seleccionados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{ $movements->links() }}
                @else
                    <p class="text-sm text-gray-500">Seleccione un producto para ver su historial de entradas y salidas.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
