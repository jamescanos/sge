@php
    // Tono de color de cada tarjeta KPI (clases completas para que Tailwind las incluya)
    $tones = [
        'brand' => 'bg-brand/10 text-brand',
        'indigo' => 'bg-indigo-50 text-indigo-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'emerald' => 'bg-emerald-50 text-emerald-600',
        'sky' => 'bg-sky-50 text-sky-600',
        'violet' => 'bg-violet-50 text-violet-600',
        'orange' => 'bg-orange-50 text-orange-600',
        'slate' => 'bg-slate-100 text-slate-600',
        'rose' => 'bg-rose-50 text-rose-600',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">Dashboard</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">

        {{-- Bienvenida con el color de marca, roles y accesos rápidos --}}
        <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-brand to-brand-dark p-6 text-white shadow-lg sm:p-8">
            <div class="absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/10"></div>
            <div class="absolute -bottom-24 right-32 h-56 w-56 rounded-full bg-white/5"></div>

            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-medium text-white/70">{{ Str::ucfirst(now()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY')) }}</p>
                    <h1 class="mt-1 text-2xl font-bold sm:text-3xl">Bienvenido, {{ auth()->user()->name }}</h1>
                    <p class="mt-2 text-sm text-white/80">
                        <strong>Roles asignados:</strong>
                        {{ $roles->isEmpty() ? 'Sin rol asignado' : $roles->join(', ') }}
                        @if ($roles->count() > 1)
                            · <strong>Rol activo:</strong> {{ $activeRole }}
                            (<a href="{{ route('active-role.edit') }}" class="underline hover:text-white">cambiar</a>)
                        @endif
                    </p>
                </div>

                {{-- Accesos rápidos (cada botón aparece solo si el usuario tiene el permiso) --}}
                <div class="flex flex-wrap gap-2">
                    @can('crear-productos')
                        <a href="{{ route('products.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-2 text-sm font-semibold text-brand shadow-sm hover:bg-brand-light">
                            <x-icon name="plus" class="h-4 w-4" /> Nuevo producto
                        </a>
                    @endcan
                    @can('crear-categorias')
                        <a href="{{ route('categories.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-white/15 px-3 py-2 text-sm font-semibold text-white ring-1 ring-white/30 hover:bg-white/25">
                            <x-icon name="plus" class="h-4 w-4" /> Nueva categoría
                        </a>
                    @endcan
                    @can('crear-ventas')
                        <a href="{{ route('sales.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-white/15 px-3 py-2 text-sm font-semibold text-white ring-1 ring-white/30 hover:bg-white/25">
                            <x-icon name="plus" class="h-4 w-4" /> Nueva venta
                        </a>
                    @endcan
                    @can('crear-compras')
                        <a href="{{ route('purchases.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-white/15 px-3 py-2 text-sm font-semibold text-white ring-1 ring-white/30 hover:bg-white/25">
                            <x-icon name="plus" class="h-4 w-4" /> Nueva orden de compra
                        </a>
                    @endcan
                </div>
            </div>
        </section>

        {{-- KPIs --}}
        @if ($stats->isNotEmpty())
            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($stats as $stat)
                    <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $tones[$stat['tone'] ?? 'slate'] }}">
                            <x-icon :name="$stat['icon'] ?? 'chart'" class="h-6 w-6" />
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-sm text-slate-500">{{ $stat['label'] }}</p>
                            <p class="text-xl font-bold text-slate-800">{{ $stat['value'] }}</p>
                            @isset($stat['hint'])
                                <p class="text-xs text-slate-400">{{ $stat['hint'] }}</p>
                            @endisset
                        </div>
                    </div>
                @endforeach
            </section>
        @endif

        {{-- Gráficos (Chart.js, ver resources/js/charts.js) --}}
        @if (count($charts))
            <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                @foreach ($charts as $chart)
                    <div @class(['rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70', 'lg:col-span-2' => $chart['wide'] ?? false])>
                        <div class="mb-4">
                            <h3 class="font-semibold text-slate-800">{{ $chart['title'] }}</h3>
                            <p class="text-xs text-slate-500">{{ $chart['subtitle'] }}</p>
                        </div>
                        @if ($chart['empty'])
                            <div class="flex h-64 flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 text-sm text-slate-400">
                                <x-icon name="chart" class="mb-2 h-8 w-8" />
                                Sin datos para mostrar todavía
                            </div>
                        @else
                            <div class="relative h-64">
                                <canvas id="chart-{{ $chart['id'] }}" data-chart='@json($chart)' aria-label="{{ $chart['title'] }}" role="img"></canvas>
                            </div>
                        @endif
                    </div>
                @endforeach
            </section>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Sugerencia de reabastecimiento (Clase 10) --}}
            @if ($lowStock->isNotEmpty())
                <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                            <x-icon name="alert" class="h-5 w-5" />
                        </span>
                        <div>
                            <h3 class="font-semibold text-slate-800">Productos para reabastecer</h3>
                            <p class="text-xs text-slate-500">Stock menor o igual a 5 unidades</p>
                        </div>
                    </div>
                    <ul class="divide-y divide-slate-100 text-sm">
                        @foreach ($lowStock as $product)
                            <li class="flex items-center justify-between py-2">
                                <span>{{ $product->name }}</span>
                                <span @class(['rounded-full px-2.5 py-0.5 text-xs font-semibold',
                                    'bg-rose-100 text-rose-700' => $product->stock === 0,
                                    'bg-amber-100 text-amber-700' => $product->stock > 0])>{{ $product->stock }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('purchases.create') }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-brand hover:underline">
                        <x-icon name="plus" class="h-4 w-4" /> Crear orden de compra
                    </a>
                </section>
            @endif

            {{-- Módulos disponibles para el usuario --}}
            <section @class(['rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70', 'lg:col-span-2' => $lowStock->isNotEmpty(), 'lg:col-span-3' => $lowStock->isEmpty()])>
                <h3 class="mb-4 font-semibold text-slate-800">Módulos</h3>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    @forelse ($modules as $module)
                        <a href="{{ route($module['route']) }}"
                           class="group flex items-start gap-3 rounded-xl border border-slate-200 p-4 transition hover:border-brand/40 hover:bg-brand-light/60">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-brand/10 text-brand transition group-hover:bg-brand group-hover:text-white">
                                <x-icon :name="$module['icon']" class="h-5 w-5" />
                            </span>
                            <span>
                                <span class="block font-semibold text-slate-800">{{ $module['title'] }}</span>
                                <span class="block text-sm text-slate-500">{{ $module['description'] }}</span>
                            </span>
                        </a>
                    @empty
                        <div class="col-span-full rounded-xl border border-dashed border-slate-300 p-8 text-center">
                            <p class="font-medium text-slate-900">No tienes módulos disponibles.</p>
                            <p class="mt-1 text-sm text-slate-600">Pide a un administrador que te asigne un rol.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
