<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nueva orden de compra</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('purchases.store') }}" id="purchase-form"
                  class="bg-white shadow rounded p-6 space-y-4">
                @csrf

                @if ($errors->any())
                    <div class="p-3 rounded bg-red-100 text-red-800">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-input-label for="provider_id" value="Proveedor" />
                        <select id="provider_id" name="provider_id" class="mt-1 block w-full border-gray-300 rounded-md" required>
                            <option value="">Seleccione un proveedor</option>
                            @foreach ($providers as $provider)
                                <option value="{{ $provider->id }}" @selected(old('provider_id') == $provider->id)>
                                    {{ $provider->name }} — {{ $provider->document_number }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="purchase_date" value="Fecha de la orden" />
                        <x-text-input id="purchase_date" name="purchase_date" type="date" class="mt-1 block w-full"
                            :value="old('purchase_date', now()->format('Y-m-d'))" required />
                    </div>

                    <div>
                        <x-input-label for="provider_invoice" value="Factura del proveedor (opcional)" />
                        <x-text-input id="provider_invoice" name="provider_invoice" type="text" class="mt-1 block w-full"
                            :value="old('provider_invoice')" />
                    </div>
                </div>

                <div>
                    <x-input-label value="Productos a comprar" />
                    <table class="min-w-full text-sm mt-2">
                        <thead>
                            <tr class="border-b text-left">
                                <th class="p-2">Producto</th>
                                <th class="p-2 w-24">Cantidad</th>
                                <th class="p-2 w-40">Costo unitario</th>
                                <th class="p-2 w-32">Subtotal</th>
                                <th class="p-2 w-16"></th>
                            </tr>
                        </thead>
                        <tbody id="items-body">
                            <!-- Filas dinámicas con JS -->
                        </tbody>
                    </table>
                    <button type="button" id="add-item" class="mt-2 text-blue-700 underline">+ Agregar producto</button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-t pt-4">
                    <div>
                        <x-input-label value="Subtotal" />
                        <p id="subtotal-display" class="text-lg font-semibold">$0.00</p>
                    </div>
                    <div>
                        <x-input-label value="IVA (19%)" />
                        <p id="tax-display" class="text-lg font-semibold">$0.00</p>
                    </div>
                    <div>
                        <x-input-label value="Total" />
                        <p id="total-display" class="text-lg font-bold text-green-700">$0.00</p>
                    </div>
                </div>

                <div>
                    <x-input-label for="notes" value="Notas (opcional)" />
                    <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full border-gray-300 rounded-md">{{ old('notes') }}</textarea>
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button>Registrar orden de compra</x-primary-button>
                    <a href="{{ route('purchases.index') }}" class="underline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Plantilla de una fila de producto para JS -->
    <template id="item-row-template">
        <tr class="border-b item-row">
            <td class="p-2">
                <select name="items[__INDEX__][product_id]" class="product-select w-full border-gray-300 rounded-md" required>
                    <option value="">Seleccione</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                            {{ $product->name }} (stock actual: {{ $product->stock }})
                        </option>
                    @endforeach
                </select>
            </td>
            <td class="p-2">
                <input type="number" name="items[__INDEX__][quantity]" value="1" min="1"
                       class="quantity-input w-full border-gray-300 rounded-md" required>
            </td>
            <td class="p-2">
                <input type="number" name="items[__INDEX__][unit_cost]" value="" min="0.01" step="0.01"
                       class="cost-input w-full border-gray-300 rounded-md" required>
                <span class="price-hint block text-xs text-gray-500"></span>
            </td>
            <td class="p-2">
                <span class="subtotal-display">$0.00</span>
            </td>
            <td class="p-2">
                <button type="button" class="remove-row text-red-700 underline">X</button>
            </td>
        </tr>
    </template>

    @push('scripts')
    <script>
        let index = 0;
        const itemsBody = document.getElementById('items-body');
        const template = document.getElementById('item-row-template').innerHTML;
        const addBtn = document.getElementById('add-item');

        // Filas enviadas antes (si el servidor devolvió un error, se reconstruyen)
        const oldItems = @json(array_values(old('items', [])));

        function addRow(item = null) {
            const html = template.replace(/__INDEX__/g, index++);
            itemsBody.insertAdjacentHTML('beforeend', html);
            const row = itemsBody.lastElementChild;
            if (item) {
                row.querySelector('.product-select').value = item.product_id ?? '';
                row.querySelector('.quantity-input').value = item.quantity ?? 1;
                row.querySelector('.cost-input').value = item.unit_cost ?? '';
            }
            bindRow(row);
            recalculate();
        }

        function bindRow(row) {
            row.querySelector('.product-select').addEventListener('change', recalculate);
            row.querySelector('.quantity-input').addEventListener('input', recalculate);
            row.querySelector('.cost-input').addEventListener('input', recalculate);
            row.querySelector('.remove-row').addEventListener('click', () => {
                row.remove();
                recalculate();
            });
        }

        function formatCOP(value) {
            return '$' + value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function recalculate() {
            let subtotal = 0;
            document.querySelectorAll('.item-row').forEach(row => {
                const select = row.querySelector('.product-select');
                const opt = select.options[select.selectedIndex];
                const salePrice = parseFloat(opt?.dataset?.price || 0);
                const qty = parseInt(row.querySelector('.quantity-input').value || 0);
                const cost = parseFloat(row.querySelector('.cost-input').value || 0);
                const lineSubtotal = cost * qty;

                // Ayuda visual: precio de venta actual del producto (el costo debería ser menor)
                const hint = row.querySelector('.price-hint');
                hint.textContent = salePrice ? 'Precio de venta: ' + formatCOP(salePrice) : '';
                hint.classList.toggle('text-red-600', cost > 0 && salePrice > 0 && cost >= salePrice);

                row.querySelector('.subtotal-display').textContent = formatCOP(lineSubtotal);
                subtotal += lineSubtotal;
            });

            const tax = subtotal * 0.19;
            const total = subtotal + tax;

            document.getElementById('subtotal-display').textContent = formatCOP(subtotal);
            document.getElementById('tax-display').textContent = formatCOP(tax);
            document.getElementById('total-display').textContent = formatCOP(total);
        }

        addBtn.addEventListener('click', () => addRow());

        if (oldItems.length) {
            oldItems.forEach(item => addRow(item));
        } else {
            addRow(); // Fila inicial
        }
    </script>
    @endpush
</x-app-layout>
