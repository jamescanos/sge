<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Factura {{ $sale->invoice_number }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded p-6 space-y-4">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Cliente</p>
                        <p class="font-semibold">{{ $sale->client?->name }}</p>
                        <p class="text-sm">{{ $sale->client?->document_type }} {{ $sale->client?->document_number }}</p>
                    </div>
                    <div class="md:text-right">
                        <p class="text-sm text-gray-500">Fecha</p>
                        <p class="font-semibold">{{ $sale->sale_date->format('d/m/Y') }}</p>
                        <p class="text-sm">Vendedor: {{ $sale->user?->name }}</p>
                    </div>
                </div>

                <table class="min-w-full text-sm border-t">
                    <thead>
                        <tr class="border-b text-left">
                            <th class="p-2">Producto</th>
                            <th class="p-2 text-right">Cantidad</th>
                            <th class="p-2 text-right">Precio unit.</th>
                            <th class="p-2 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sale->details as $detail)
                            <tr class="border-b">
                                <td class="p-2">{{ $detail->product?->name }}</td>
                                <td class="p-2 text-right">{{ $detail->quantity }}</td>
                                <td class="p-2 text-right">${{ number_format((float) $detail->unit_price, 2) }}</td>
                                <td class="p-2 text-right">${{ number_format((float) $detail->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="flex justify-end">
                    <div class="text-right space-y-1">
                        <p>Subtotal: ${{ number_format((float) $sale->subtotal, 2) }}</p>
                        <p>IVA (19%): ${{ number_format((float) $sale->tax, 2) }}</p>
                        <p class="text-lg font-bold text-green-700">Total: ${{ number_format((float) $sale->total, 2) }}</p>
                    </div>
                </div>

                <div class="flex gap-4 border-t pt-4">
                    <a href="{{ route('sales.index') }}" class="underline">← Volver al listado</a>
                    <a href="{{ route('sales.pdf', $sale) }}" target="_blank" class="text-blue-700 underline">Descargar PDF</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
