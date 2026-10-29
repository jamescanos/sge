<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Orden de compra {{ $purchase->purchase_number }}
            <span class="ml-2 text-sm font-medium text-gray-600">({{ ucfirst($purchase->status) }})</span>
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded p-6 space-y-4">

                @if ($errors->any())
                    <div class="p-3 rounded bg-red-100 text-red-800">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Proveedor</p>
                        <p class="font-semibold">{{ $purchase->provider?->name }}</p>
                        <p class="text-sm">{{ $purchase->provider?->document_type }} {{ $purchase->provider?->document_number }}</p>
                        <p class="text-sm">Contacto: {{ $purchase->provider?->contact_name ?? '—' }}</p>
                    </div>
                    <div class="md:text-right">
                        <p class="text-sm text-gray-500">Fecha de la orden</p>
                        <p class="font-semibold">{{ $purchase->purchase_date->format('d/m/Y') }}</p>
                        <p class="text-sm">Registró: {{ $purchase->user?->name }}</p>
                        <p class="text-sm">Factura proveedor: {{ $purchase->provider_invoice ?? '—' }}</p>
                        @if ($purchase->received_at)
                            <p class="text-sm">Recibida completa: {{ $purchase->received_at->format('d/m/Y H:i') }}</p>
                        @endif
                    </div>
                </div>

                @php
                    $canReceive = $purchase->canBeReceived() && auth()->user()->can('recibir-compras');
                @endphp

                <form method="POST" action="{{ route('purchases.receive', $purchase) }}">
                    @csrf
                    @method('PATCH')

                    <table class="min-w-full text-sm border-t">
                        <thead>
                            <tr class="border-b text-left">
                                <th class="p-2">Producto</th>
                                <th class="p-2 text-right">Pedida</th>
                                <th class="p-2 text-right">Recibida</th>
                                <th class="p-2 text-right">Pendiente</th>
                                <th class="p-2 text-right">Costo unit.</th>
                                <th class="p-2 text-right">Subtotal</th>
                                @if ($canReceive)
                                    <th class="p-2 w-32">Llega ahora</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($purchase->details as $detail)
                                <tr class="border-b">
                                    <td class="p-2">{{ $detail->product?->name }}</td>
                                    <td class="p-2 text-right">{{ $detail->quantity }}</td>
                                    <td class="p-2 text-right">{{ $detail->received_quantity }}</td>
                                    <td class="p-2 text-right">{{ $detail->pendingQuantity() }}</td>
                                    <td class="p-2 text-right">${{ number_format((float) $detail->unit_cost, 2) }}</td>
                                    <td class="p-2 text-right">${{ number_format((float) $detail->subtotal, 2) }}</td>
                                    @if ($canReceive)
                                        <td class="p-2">
                                            <input type="number" name="items[{{ $detail->id }}]" min="0"
                                                   max="{{ $detail->pendingQuantity() }}"
                                                   value="{{ old('items.'.$detail->id, $detail->pendingQuantity()) }}"
                                                   class="w-full border-gray-300 rounded-md"
                                                   @disabled($detail->pendingQuantity() === 0)>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @if ($canReceive)
                        <div class="flex flex-wrap items-end gap-4 mt-4 p-4 rounded bg-gray-50">
                            <div>
                                <x-input-label for="provider_invoice" value="Factura del proveedor (opcional)" />
                                <x-text-input id="provider_invoice" name="provider_invoice" type="text" class="mt-1"
                                    :value="old('provider_invoice', $purchase->provider_invoice)" />
                            </div>
                            <x-primary-button onclick="return confirm('¿Registrar la recepción? El stock de los productos aumentará.')">
                                Registrar recepción
                            </x-primary-button>
                        </div>
                    @endif
                </form>

                <div class="flex justify-end">
                    <div class="text-right space-y-1">
                        <p>Subtotal: ${{ number_format((float) $purchase->subtotal, 2) }}</p>
                        <p>IVA (19%): ${{ number_format((float) $purchase->tax, 2) }}</p>
                        <p class="text-lg font-bold text-green-700">Total: ${{ number_format((float) $purchase->total, 2) }}</p>
                    </div>
                </div>

                @if ($purchase->notes)
                    <p class="text-sm text-gray-600"><strong>Notas:</strong> {{ $purchase->notes }}</p>
                @endif

                <div class="flex gap-4 border-t pt-4">
                    <a href="{{ route('purchases.index') }}" class="underline">← Volver al listado</a>
                    <a href="{{ route('purchases.pdf', $purchase) }}" target="_blank" class="text-blue-700 underline">Descargar PDF</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
