<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Productos</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ $showTrashed ? 'Productos eliminados. Puedes restaurarlos.' : 'Inventario de productos por categoría.' }}
                </p>
            </div>
            @can('crear-productos')
                <a href="{{ route('products.create') }}" class="inline-flex items-center gap-1.5 rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-teal-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-600">
                    <span aria-hidden="true">+</span> Nuevo producto
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-4 px-4 py-6 sm:px-6 lg:px-8">
        @can('eliminar-productos')
            <x-trash-tabs route="products.index" :show-trashed="$showTrashed" :trashed-count="$trashedCount" />
        @endcan

        <form method="GET" action="{{ route('products.index') }}" class="grid grid-cols-1 gap-3 rounded-md border border-gray-200 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-[1fr_14rem_12rem_auto] dark:border-gray-700 dark:bg-gray-800">
            @if ($showTrashed)
                <input type="hidden" name="trashed" value="1">
            @endif
            <div>
                <label for="search" class="sr-only">Buscar</label>
                <x-text-input id="search" name="search" type="search" class="block w-full" placeholder="Buscar por nombre o descripción" :value="request('search')" />
            </div>
            <div>
                <label for="category" class="sr-only">Categoría</label>
                <x-select id="category" name="category" class="block w-full">
                    <option value="">Todas las categorías</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <label for="status" class="sr-only">Estado</label>
                <x-select id="status" name="status" class="block w-full">
                    <option value="">Todos los estados</option>
                    <option value="active" @selected(request('status') === 'active')>Activos</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactivos</option>
                </x-select>
            </div>
            <div class="flex gap-2">
                <x-primary-button class="justify-center">Filtrar</x-primary-button>
                @if (request()->hasAny(['search', 'category', 'status']))
                    <a href="{{ route('products.index', $showTrashed ? ['trashed' => 1] : []) }}" class="inline-flex items-center rounded-md px-3 py-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">Limpiar</a>
                @endif
            </div>
        </form>

        <div class="overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            <th scope="col" class="px-4 py-3">Producto</th>
                            <th scope="col" class="px-4 py-3">Categoría</th>
                            <th scope="col" class="px-4 py-3 text-right">Precio</th>
                            <th scope="col" class="px-4 py-3 text-right">Stock</th>
                            <th scope="col" class="px-4 py-3">Estado</th>
                            <th scope="col" class="px-4 py-3 text-right"><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($products as $product)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                <td class="max-w-xs px-4 py-3">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $product->name }}</div>
                                    @if ($product->description)
                                        <div class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $product->description }}</div>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-300">
                                    {{ $product->category?->name ?? '—' }}
                                    @if ($product->category?->trashed())
                                        <span class="text-xs text-gray-500">(en papelera)</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums text-gray-900 dark:text-gray-100">$ {{ number_format((float) $product->price, 2) }}</td>
                                <td @class([
                                    'whitespace-nowrap px-4 py-3 text-right tabular-nums',
                                    'font-semibold text-red-700 dark:text-red-400' => $product->stock <= 5,
                                    'text-gray-900 dark:text-gray-100' => $product->stock > 5,
                                ])>{{ $product->stock }}</td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <x-status-badge :active="$product->active" :trashed="$product->trashed()" />
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <div class="flex justify-end gap-3">
                                        @if ($product->trashed())
                                            <form method="POST" action="{{ route('products.restore', $product) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="font-medium text-teal-700 hover:text-teal-900 dark:text-teal-400 dark:hover:text-teal-300">Restaurar</button>
                                            </form>
                                        @else
                                            @can('editar-productos')
                                                <a href="{{ route('products.edit', $product) }}" class="font-medium text-teal-700 hover:text-teal-900 dark:text-teal-400 dark:hover:text-teal-300">Editar</a>
                                            @endcan
                                            @can('eliminar-productos')
                                                <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm(@js('¿Enviar «'.$product->name.'» a la papelera?'))">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">Eliminar</button>
                                                </form>
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                                    {{ $showTrashed ? 'La papelera está vacía.' : 'No hay productos que coincidan con la búsqueda.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $products->links() }}
    </div>
</x-app-layout>
