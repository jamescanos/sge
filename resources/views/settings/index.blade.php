<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Configuración — Seguridad</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow rounded p-4 text-sm text-gray-700">
                <strong>¿Cómo se calculan los permisos?</strong>
                Permisos efectivos = permisos del <strong>rol activo</strong> + permisos <strong>directos</strong> del usuario.
                Un usuario con varios roles elige con cuál trabajar al iniciar sesión.
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                @can('ver-usuarios')
                    <a href="{{ route('settings.users.index') }}" class="bg-white shadow rounded p-6 hover:shadow-lg transition">
                        <h3 class="font-semibold text-lg text-brand">Usuarios</h3>
                        <p class="text-3xl font-bold mt-2">{{ $usersCount }}</p>
                        <p class="text-sm text-gray-600 mt-1">{{ $inactiveCount }} inactivos · {{ $multiRoleCount }} con varios roles</p>
                    </a>
                @endcan
                @can('ver-roles')
                    <a href="{{ route('settings.roles.index') }}" class="bg-white shadow rounded p-6 hover:shadow-lg transition">
                        <h3 class="font-semibold text-lg text-brand">Roles</h3>
                        <p class="text-3xl font-bold mt-2">{{ $rolesCount }}</p>
                        <p class="text-sm text-gray-600 mt-1">Perfiles de trabajo y sus permisos.</p>
                    </a>
                @endcan
                @can('ver-permisos')
                    <a href="{{ route('settings.permissions.index') }}" class="bg-white shadow rounded p-6 hover:shadow-lg transition">
                        <h3 class="font-semibold text-lg text-brand">Matriz de permisos</h3>
                        <p class="text-3xl font-bold mt-2">{{ $permissionsCount }}</p>
                        <p class="text-sm text-gray-600 mt-1">Permisos por rol, módulo y usuario.</p>
                    </a>
                @endcan
                {{-- Configuración visual: nombre, logo, NIT, razón social y color de marca --}}
                @can('ver-branding')
                    <a href="{{ route('settings.branding.edit') }}" class="bg-white shadow rounded p-6 hover:shadow-lg transition">
                        <h3 class="font-semibold text-lg text-brand">Identidad corporativa</h3>
                        <div class="mt-2 flex items-center gap-3">
                            <x-brand-logo class="h-10 w-10" />
                            <span class="min-w-0">
                                <span class="block truncate font-semibold">{{ $branding->trade_name }}</span>
                                <span class="block text-xs text-gray-500">NIT {{ $branding->nit }}</span>
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 mt-2">Nombre, logo, razón social y color de marca.</p>
                    </a>
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>
