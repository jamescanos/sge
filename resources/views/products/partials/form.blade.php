<div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-input-label for="name" value="Nombre" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" maxlength="150" :value="old('name', $product->name)" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="description" value="Descripción (opcional)" />
        <x-textarea id="description" name="description" rows="3" class="mt-1 block w-full">{{ old('description', $product->description) }}</x-textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>

    <div>
        <x-input-label for="price" value="Precio" />
        <div class="relative mt-1">
            <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-sm text-gray-500">$</span>
            <x-text-input id="price" name="price" type="number" step="0.01" min="0" class="block w-full ps-7" :value="old('price', $product->price)" required />
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('price')" />
    </div>

    <div>
        @if ($product->exists)
            {{-- Clase 12: el stock solo cambia por movimientos de inventario --}}
            <x-input-label value="Stock actual" />
            <p class="mt-1 text-lg font-semibold">{{ $product->stock }}</p>
            <div class="mt-1 space-x-3 text-sm">
                @can('ver-inventario')
                    <a href="{{ route('inventory.kardex', ['product' => $product->id]) }}" class="text-blue-700 underline">Ver kardex</a>
                @endcan
                @can('ajustar-inventario')
                    <a href="{{ route('inventory.adjust', $product) }}" class="text-blue-700 underline">Ajustar inventario</a>
                @endcan
            </div>
        @else
            <x-input-label for="stock" value="Stock inicial" />
            <x-text-input id="stock" name="stock" type="number" step="1" min="0" class="mt-1 block w-full" :value="old('stock', $product->stock)" required />
            <x-input-error class="mt-2" :messages="$errors->get('stock')" />
        @endif
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="category_id" value="Categoría" />
        <x-select id="category_id" name="category_id" class="mt-1 block w-full" required>
            <option value="">Selecciona una categoría</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
            @endforeach
        </x-select>
        @if ($categories->isEmpty())
            <p class="mt-2 text-sm text-amber-700 dark:text-amber-400">No hay categorías activas. Crea o activa una antes de registrar productos.</p>
        @endif
        <x-input-error class="mt-2" :messages="$errors->get('category_id')" />
    </div>

    <div class="sm:col-span-2">
        <label for="active" class="inline-flex items-center gap-2">
            <input id="active" name="active" type="checkbox" value="1" class="rounded border-gray-300 text-teal-700 shadow-sm focus:ring-teal-600 dark:border-gray-700 dark:bg-gray-900" @checked(old('active', $product->active))>
            <span class="text-sm text-gray-700 dark:text-gray-300">Producto activo</span>
        </label>
        <x-input-error class="mt-2" :messages="$errors->get('active')" />
    </div>
</div>
