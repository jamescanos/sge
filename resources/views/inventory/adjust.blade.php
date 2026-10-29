<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ajuste de inventario: {{ $product->name }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <form method="POST" action="{{ route('inventory.adjust.store', $product) }}" class="bg-white shadow rounded p-6 space-y-4">
                @csrf

                <p class="text-sm text-gray-600">
                    Un ajuste se hace después de un <strong>conteo físico</strong>: se digita la cantidad que realmente hay
                    en bodega y el sistema registra la diferencia como entrada o salida en el kardex.
                </p>

                <div class="rounded bg-gray-50 p-3">
                    Stock en el sistema: <strong class="text-lg">{{ $product->stock }}</strong>
                </div>

                <div>
                    <x-input-label for="counted_stock" value="Cantidad contada en bodega" />
                    <x-text-input id="counted_stock" name="counted_stock" type="number" min="0" step="1" class="mt-1 block w-48"
                        :value="old('counted_stock', $product->stock)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('counted_stock')" />
                </div>

                <div>
                    <x-input-label for="reason" value="Motivo del ajuste (obligatorio)" />
                    <x-textarea id="reason" name="reason" rows="2" class="mt-1 block w-full"
                        placeholder="Ej.: Conteo mensual de octubre: 1 unidad dañada por humedad" required>{{ old('reason') }}</x-textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('reason')" />
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button onclick="return confirm('¿Registrar el ajuste de inventario?')">Registrar ajuste</x-primary-button>
                    <a href="{{ route('inventory.kardex', ['product' => $product->id]) }}" class="underline">Cancelar</a>
                </div>
            </form>

            <div class="bg-white shadow rounded p-6">
                <h3 class="font-semibold mb-2">Últimos movimientos</h3>
                <ul class="text-sm space-y-1">
                    @forelse ($lastMovements as $m)
                        <li>{{ $m->created_at->format('d/m/Y H:i') }} — {{ $m->conceptLabel() }}: {{ $m->type }} {{ $m->quantity }} → saldo {{ $m->balance }}</li>
                    @empty
                        <li class="text-gray-500">Sin movimientos.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
