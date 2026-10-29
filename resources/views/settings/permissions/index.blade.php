<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Configuración — Matriz de permisos</h2>
    </x-slot>

    @php
        $canEdit = auth()->user()->can('editar-roles');
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow rounded p-6 space-y-4">
                <a href="{{ route('settings.index') }}" class="underline">← Configuración</a>

                {{-- Filtros --}}
                <form method="GET" action="{{ route('settings.permissions.index') }}" class="flex flex-wrap items-end gap-2">
                    <div>
                        <x-input-label for="module" value="Módulo (categoría)" />
                        <select id="module" name="module" class="mt-1 border-gray-300 rounded-md">
                            <option value="">Todos los módulos</option>
                            @foreach ($modules as $key => $label)
                                <option value="{{ $key }}" @selected($module === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="user" value="Ver como usuario" />
                        <select id="user" name="user" class="mt-1 border-gray-300 rounded-md">
                            <option value="">— Ninguno —</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" @selected($inspected?->id === $u->id)>{{ $u->name }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <x-primary-button>Aplicar</x-primary-button>
                </form>

                @if ($inspected)
                    <div class="rounded bg-blue-50 p-3 text-sm text-blue-900">
                        <strong>{{ $inspected->name }}</strong> — roles:
                        {{ $inspected->roles->pluck('name')->join(', ') ?: 'ninguno' }}.
                        La última columna muestra sus permisos efectivos y de dónde vienen.
                        @if ($inspected->roles->count() > 1)
                            Como tiene varios roles, en cada sesión solo aplica el rol que elija + sus permisos directos.
                        @endif
                    </div>
                @endif

                {{-- Matriz: filas = permisos por módulo, columnas = roles --}}
                <form method="POST" action="{{ route('settings.permissions.matrix') }}">
                    @csrf
                    @method('PUT')

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border">
                            <thead class="bg-gray-50">
                                <tr class="text-left">
                                    <th class="p-2 border-b">Permiso</th>
                                    <th class="p-2 border-b">Acción</th>
                                    @foreach ($roles as $role)
                                        <th class="p-2 border-b text-center capitalize">{{ $role->name }}</th>
                                    @endforeach
                                    <th class="p-2 border-b">Usuarios con permiso directo</th>
                                    @if ($inspected)
                                        <th class="p-2 border-b bg-blue-50">{{ $inspected->name }}</th>
                                    @endif
                                    @can('eliminar-permisos')
                                        <th class="p-2 border-b"></th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($groups as $key => $group)
                                    <tr class="bg-gray-100">
                                        <td colspan="{{ 4 + $roles->count() + ($inspected ? 1 : 0) }}" class="p-2 font-semibold">
                                            {{ $group['label'] }}
                                        </td>
                                    </tr>
                                    @foreach ($group['permissions'] as $action => $permission)
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="p-2 font-mono text-xs">
                                                {{ $permission->name }}
                                                <input type="hidden" name="visible[]" value="{{ $permission->name }}">
                                            </td>
                                            <td class="p-2">{{ \App\Support\PermissionCatalog::actionLabel($action) }}</td>
                                            @foreach ($roles as $role)
                                                <td class="p-2 text-center">
                                                    <input type="checkbox" name="matrix[{{ $role->id }}][]" value="{{ $permission->name }}"
                                                           class="rounded border-gray-300 text-blue-600"
                                                           @checked(in_array($permission->name, $rolePermissions[$role->id]))
                                                           @disabled(! $canEdit || $role->name === $superRole)>
                                                </td>
                                            @endforeach
                                            <td class="p-2 text-xs text-gray-600">
                                                {{ $permission->users->pluck('name')->join(', ') ?: '—' }}
                                            </td>
                                            @if ($inspected)
                                                <td class="p-2 text-xs bg-blue-50">
                                                    @if (isset($origins[$permission->name]))
                                                        <span class="text-green-700 font-semibold">✓</span> {{ implode(' · ', $origins[$permission->name]) }}
                                                    @else
                                                        <span class="text-gray-400">✗</span>
                                                    @endif
                                                </td>
                                            @endif
                                            @can('eliminar-permisos')
                                                <td class="p-2">
                                                    <button type="submit" form="delete-{{ $permission->id }}" class="text-xs text-red-700 underline">Eliminar</button>
                                                </td>
                                            @endcan
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($canEdit)
                        <div class="mt-4 flex items-center gap-4">
                            <x-primary-button>Guardar matriz</x-primary-button>
                            <span class="text-xs text-gray-500">La columna «{{ $superRole }}» no se edita: siempre tiene todos los permisos.</span>
                        </div>
                    @endif
                </form>

                {{-- Formularios de eliminación (fuera del formulario de la matriz: HTML no permite formularios anidados) --}}
                @can('eliminar-permisos')
                    @foreach ($groups as $group)
                        @foreach ($group['permissions'] as $permission)
                            <form id="delete-{{ $permission->id }}" method="POST" action="{{ route('settings.permissions.destroy', $permission) }}"
                                  onsubmit="return confirm('¿Eliminar el permiso {{ $permission->name }}?')">
                                @csrf @method('DELETE')
                            </form>
                        @endforeach
                    @endforeach
                @endcan
            </div>

            {{-- Crear permiso --}}
            @can('crear-permisos')
                <div class="bg-white shadow rounded p-6 space-y-3">
                    <h3 class="font-semibold text-lg">Nuevo permiso</h3>
                    <p class="text-sm text-gray-600">
                        Se crea con la convención <code>accion-modulo</code> y se asigna automáticamente al rol {{ $superRole }}.
                        Recuerde que un permiso nuevo solo tiene efecto cuando el código lo verifica
                        (middleware <code>permission:</code> o <code>@@can</code>).
                    </p>
                    <form method="POST" action="{{ route('settings.permissions.store') }}" class="flex flex-wrap items-end gap-2">
                        @csrf
                        <div>
                            <x-input-label for="action" value="Acción" />
                            <x-text-input id="action" name="action" list="actions-list" class="mt-1" :value="old('action')" placeholder="aprobar" required />
                            <datalist id="actions-list">
                                @foreach (config('permisos.actions') as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </datalist>
                        </div>
                        <div>
                            <x-input-label for="new-module" value="Módulo" />
                            <x-text-input id="new-module" name="module" list="modules-list" class="mt-1" :value="old('module')" placeholder="compras" required />
                            <datalist id="modules-list">
                                @foreach ($modules as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </datalist>
                        </div>
                        <x-primary-button>Crear permiso</x-primary-button>
                    </form>
                    <x-input-error :messages="$errors->get('action')" />
                    <x-input-error :messages="$errors->get('module')" />
                    <x-input-error :messages="$errors->get('name')" />
                </div>
            @endcan
        </div>
    </div>
</x-app-layout>
