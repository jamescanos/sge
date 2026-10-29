{{-- ============================================================
     MENÚ LATERAL (sidebar): fijo en pantallas grandes y desplegable en celulares.
     Cada enlace aparece solo si el usuario tiene el permiso (@can).
     ============================================================ --}}

{{-- Fondo oscuro detrás del menú cuando se abre en el celular --}}
<div x-cloak x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
     class="fixed inset-0 z-40 bg-slate-900/60 lg:hidden"></div>

{{-- Oculto a la izquierda en el celular; !translate-x-0 (con prioridad) lo muestra al abrirlo --}}
<aside :class="{ '!translate-x-0': sidebarOpen }"
       class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col bg-slate-900 transition-transform duration-200 lg:translate-x-0">

    {{-- Marca: logo, nombre comercial y eslogan --}}
    <div class="flex h-20 shrink-0 items-center gap-3 border-b border-white/10 px-5">
        <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3">
            <x-brand-logo class="h-11 w-11 shrink-0" />
            <span class="min-w-0">
                <span class="block truncate text-base font-bold text-white">{{ $branding->trade_name }}</span>
                @if ($branding->slogan)
                    <span class="block truncate text-xs text-slate-400">{{ $branding->slogan }}</span>
                @endif
            </span>
        </a>
        <button type="button" @click="sidebarOpen = false" class="ms-auto rounded-md p-1 text-slate-400 hover:text-white lg:hidden" aria-label="Cerrar menú">
            <x-icon name="close" class="h-6 w-6" />
        </button>
    </div>

    {{-- Enlaces agrupados por área de la empresa --}}
    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" aria-label="Menú principal">
        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="home">
            {{ __('Dashboard') }}
        </x-nav-link>

        @canany(['ver-productos', 'ver-categorias', 'ver-inventario'])
            <p class="px-3 pb-1 pt-5 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Inventario</p>
        @endcanany
        @can('ver-productos')
            <x-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')" icon="cube">
                Productos
            </x-nav-link>
        @endcan
        @can('ver-categorias')
            <x-nav-link :href="route('categories.index')" :active="request()->routeIs('categories.*')" icon="tag">
                Categorías
            </x-nav-link>
        @endcan
        @can('ver-inventario')
            <x-nav-link :href="route('inventory.kardex')" :active="request()->routeIs('inventory.*')" icon="archive">
                Kardex
            </x-nav-link>
        @endcan

        @canany(['ver-clientes', 'ver-ventas'])
            <p class="px-3 pb-1 pt-5 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Comercial</p>
        @endcanany
        @can('ver-clientes')
            <x-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')" icon="users">
                Clientes
            </x-nav-link>
        @endcan
        @can('ver-ventas')
            <x-nav-link :href="route('sales.index')" :active="request()->routeIs('sales.*')" icon="cart">
                Ventas
            </x-nav-link>
        @endcan

        @canany(['ver-proveedores', 'ver-compras'])
            <p class="px-3 pb-1 pt-5 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Abastecimiento</p>
        @endcanany
        @can('ver-proveedores')
            <x-nav-link :href="route('providers.index')" :active="request()->routeIs('providers.*')" icon="building">
                Proveedores
            </x-nav-link>
        @endcan
        @can('ver-compras')
            <x-nav-link :href="route('purchases.index')" :active="request()->routeIs('purchases.*')" icon="truck">
                Compras
            </x-nav-link>
        @endcan

        @canany(['ver-auditoria', 'ver-usuarios', 'ver-roles', 'ver-permisos', 'ver-branding'])
            <p class="px-3 pb-1 pt-5 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Administración</p>
        @endcanany
        @can('ver-auditoria')
            <x-nav-link :href="route('audit.index')" :active="request()->routeIs('audit.*')" icon="shield">
                Auditoría
            </x-nav-link>
        @endcan
        @canany(['ver-usuarios', 'ver-roles', 'ver-permisos'])
            <x-nav-link :href="route('settings.index')" :active="request()->routeIs('settings.*') && ! request()->routeIs('settings.branding.*')" icon="settings">
                Configuración
            </x-nav-link>
        @endcanany
        @can('ver-branding')
            <x-nav-link :href="route('settings.branding.edit')" :active="request()->routeIs('settings.branding.*')" icon="swatch">
                Identidad corporativa
            </x-nav-link>
        @endcan
    </nav>

    {{-- Opciones del usuario en pantallas pequeñas (en pantallas grandes están en la barra superior) --}}
    <div class="border-t border-white/10 p-4 lg:hidden">
        <div class="px-3">
            <div class="text-sm font-semibold text-white">{{ Auth::user()->name }}</div>
            <div class="text-xs text-slate-400">{{ Auth::user()->email }}</div>
        </div>

        <div class="mt-3 space-y-1">
            @if (Auth::user()->hasMultipleRoles())
                <x-responsive-nav-link :href="route('active-role.edit')">
                    Cambiar rol
                </x-responsive-nav-link>
            @endif
            <x-responsive-nav-link :href="route('profile.edit')">
                Mi perfil
            </x-responsive-nav-link>

            <!-- Authentication -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault();
                                    this.closest('form').submit();">
                    Cerrar sesión
                </x-responsive-nav-link>
            </form>
        </div>
    </div>

    <div class="hidden border-t border-white/10 px-5 py-3 text-xs text-slate-500 lg:block">
        © {{ date('Y') }} {{ $branding->legal_name }}
    </div>
</aside>

{{-- ============================================================
     BARRA SUPERIOR: botón del menú (celular), título de la página y usuario.
     ============================================================ --}}
<div class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur lg:pl-72">
    <div class="flex min-h-[4rem] items-center gap-4 px-4 py-3 sm:px-6 lg:px-8">
        <button type="button" @click="sidebarOpen = true" class="-ms-1 rounded-md p-1 text-slate-500 hover:text-slate-800 lg:hidden" aria-label="Abrir menú">
            <x-icon name="menu" class="h-6 w-6" />
        </button>

        {{-- Título de la página (slot «header» de cada vista) --}}
        <div class="min-w-0 flex-1">
            @isset($header)
                {{ $header }}
            @endisset
        </div>

        <!-- Settings Dropdown -->
        <div class="hidden sm:flex sm:items-center sm:ms-6">
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button class="flex items-center gap-3 rounded-full py-1 pe-2 ps-1 text-start transition hover:bg-slate-100 focus:outline-none">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand/10 text-sm font-bold text-brand">
                            {{ Str::of(Auth::user()->name)->substr(0, 1)->upper() }}
                        </span>
                        <span class="hidden md:block">
                            <span class="block text-sm font-semibold leading-tight text-slate-800">{{ Auth::user()->name }}</span>
                            @if ($activeRole = Auth::user()->activeRole())
                                <span class="block text-xs capitalize leading-tight text-brand">{{ $activeRole->name }}</span>
                            @endif
                        </span>
                        <x-icon name="chevron-down" class="h-4 w-4 text-slate-400" />
                    </button>
                </x-slot>

                <x-slot name="content">
                    @if (Auth::user()->hasMultipleRoles())
                        <x-dropdown-link :href="route('active-role.edit')">
                            Cambiar rol
                        </x-dropdown-link>
                    @endif
                    <x-dropdown-link :href="route('profile.edit')">
                        Mi perfil
                    </x-dropdown-link>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            Cerrar sesión
                        </x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>
    </div>
</div>
