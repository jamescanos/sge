<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Categorías</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ $showTrashed ? 'Categorías eliminadas. Puedes restaurarlas.' : 'Clasificación de los productos del inventario.' }}
                </p>
            </div>
            @can('crear-categorias')
                <a href="{{ route('categories.create') }}" class="inline-flex items-center gap-1.5 rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-teal-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-600">
                    <span aria-hidden="true">+</span> Nueva categoría
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-4 px-4 py-6 sm:px-6 lg:px-8">
        @can('eliminar-categorias')
            <x-trash-tabs route="categories.index" :show-trashed="$showTrashed" :trashed-count="$trashedCount" />
        @endcan

        <form method="GET" action="{{ route('categories.index') }}" class="grid grid-cols-1 gap-3 rounded-md border border-gray-200 bg-white p-4 shadow-sm sm:grid-cols-[1fr_12rem_auto] dark:border-gray-700 dark:bg-gray-800">
            @if ($showTrashed)
                <input type="hidden" name="trashed" value="1">
            @endif
            <div>
                <label for="search" class="sr-only">Buscar</label>
                <x-text-input id="search" name="search" type="search" class="block w-full" placeholder="Buscar por nombre" :value="request('search')" />
            </div>
            <div>
                <label for="status" class="sr-only">Estado</label>
                <x-select id="status" name="status" class="block w-full">
                    <option value="">Todos los estados</option>
                    <option value="active" @selected(request('status') === 'active')>Activas</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactivas</option>
                </x-select>
            </div>
            <div class="flex gap-2">
                <x-primary-button class="justify-center">Filtrar</x-primary-button>
                @if (request()->hasAny(['search', 'status']))
                    <a href="{{ route('categories.index', $showTrashed ? ['trashed' => 1] : []) }}" class="inline-flex items-center rounded-md px-3 py-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">Limpiar</a>
                @endif
            </div>
        </form>

        <div class="overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            <th scope="col" class="px-4 py-3">Categoría</th>
                            <th scope="col" class="px-4 py-3 text-right">Productos</th>
                            <th scope="col" class="px-4 py-3">Estado</th>
                            <th scope="col" class="px-4 py-3 text-right"><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($categories as $category)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                <td class="max-w-md px-4 py-3">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $category->name }}</div>
                                    @if ($category->description)
                                        <div class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $category->description }}</div>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums text-gray-900 dark:text-gray-100">
                                    @can('ver-productos')
                                        <a href="{{ route('products.index', ['category' => $category->id]) }}" class="hover:text-teal-700 hover:underline dark:hover:text-teal-300">{{ $category->products_count }}</a>
                                    @else
                                        {{ $category->products_count }}
                                    @endcan
                                </td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <x-status-badge :active="$category->active" :trashed="$category->trashed()" />
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <div class="flex justify-end gap-3">
                                        @if ($category->trashed())
                                            <form method="POST" action="{{ route('categories.restore', $category) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="font-medium text-teal-700 hover:text-teal-900 dark:text-teal-400 dark:hover:text-teal-300">Restaurar</button>
                                            </form>
                                        @else
                                            @can('editar-categorias')
                                                <a href="{{ route('categories.edit', $category) }}" class="font-medium text-teal-700 hover:text-teal-900 dark:text-teal-400 dark:hover:text-teal-300">Editar</a>
                                            @endcan
                                            @can('eliminar-categorias')
                                                <form method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm(@js('¿Enviar «'.$category->name.'» a la papelera?'))">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="font-medium text-red-600 hover:text-red-800 disabled:cursor-not-allowed disabled:text-gray-400 dark:text-red-400 dark:hover:text-red-300"
                                                        @disabled($category->products_count > 0)
                                                        @if ($category->products_count > 0) title="Tiene productos asociados" @endif>Eliminar</button>
                                                </form>
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                                    {{ $showTrashed ? 'La papelera está vacía.' : 'No hay categorías que coincidan con la búsqueda.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $categories->links() }}
    </div>
</x-app-layout>
