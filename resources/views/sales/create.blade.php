<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nueva venta</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('sales.store') }}" id="sale-form"
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="client_id" value="Cliente" />
                        <select id="client_id" name="client_id" class="mt-1 block w-full border-gray-300 rounded-md" required>
                            <option value="">Seleccione un cliente</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>
                                    {{ $client->name }} — {{ $client->document_number }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="sale_date" value="Fecha de venta" />
                        <x-text-input id="sale_date" name="sale_date" type="date" class="mt-1 block w-full"
                            :value="old('sale_date', now()->format('Y-m-d'))" required />
                    </div>
                </div>

                <div>
                    <x-input-label value="Productos" />
                    <table class="min-w-full text-sm mt-2">
                        <thead>
                            <tr class="border-b text-left">
                                <th class="p-2">Producto</th>
                                <th class="p-2 w-24">Cantidad</th>
                                <th class="p-2 w-32">Precio</th>
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
                    <x-primary-button>Registrar venta</x-primary-button>
                    <a href="{{ route('sales.index') }}" class="underline">Cancelar</a>
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
                        <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-stock="{{ $product->stock }}">
                            {{ $product->name }} (stock: {{ $product->stock }})
                        </option>
                    @endforeach
                </select>
            </td>
            <td class="p-2">
                <input type="number" name="items[__INDEX__][quantity]" value="1" min="1"
                       class="quantity-input w-full border-gray-300 rounded-md" required>
            </td>
            <td class="p-2">
                <span class="price-display">$0.00</span>
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
            }
            bindRow(row);
            recalculate();
        }

        function bindRow(row) {
            row.querySelector('.product-select').addEventListener('change', recalculate);
            row.querySelector('.quantity-input').addEventListener('input', recalculate);
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
                const qty = parseInt(row.querySelector('.quantity-input').value || 0);
                const opt = select.options[select.selectedIndex];
                const price = parseFloat(opt?.dataset?.price || 0);
                const lineSubtotal = price * qty;

                row.querySelector('.price-display').textContent = formatCOP(price);
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
