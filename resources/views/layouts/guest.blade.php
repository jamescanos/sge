<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $branding->trade_name }} · ERP</title>
        @if ($branding->logoUrl())
            <link rel="icon" href="{{ $branding->logoUrl() }}">
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <x-brand-styles />
    </head>
    <body class="font-sans text-slate-800 antialiased">
        <div class="grid min-h-screen lg:grid-cols-2">
            {{-- Panel de marca (solo en pantallas grandes) --}}
            <div class="relative hidden overflow-hidden bg-gradient-to-br from-brand to-brand-dark p-12 text-white lg:flex lg:flex-col lg:justify-between">
                <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-white/10"></div>
                <div class="absolute -bottom-32 -left-20 h-96 w-96 rounded-full bg-white/5"></div>

                <div class="relative flex items-center gap-3">
                    <x-brand-logo class="h-12 w-12 ring-2 ring-white/40" />
                    <span class="text-xl font-bold">{{ $branding->trade_name }}</span>
                </div>

                <div class="relative">
                    <h1 class="text-4xl font-bold leading-tight">{{ $branding->slogan ?: 'Sistema de gestión empresarial' }}</h1>
                    <p class="mt-4 max-w-md text-white/80">
                        Inventario, ventas, compras y auditoría en un solo lugar.
                    </p>
                </div>

                <p class="relative text-sm text-white/70">{{ $branding->legal_name }} · NIT {{ $branding->nit }}</p>
            </div>

            {{-- Formulario --}}
            <div class="flex flex-col items-center justify-center bg-slate-100 px-6 py-12">
                <a href="/" class="mb-6 flex items-center gap-3 lg:hidden">
                    <x-brand-logo class="h-12 w-12" />
                    <span class="text-xl font-bold text-slate-800">{{ $branding->trade_name }}</span>
                </a>

                <div class="w-full max-w-md rounded-2xl bg-white px-8 py-8 shadow-xl shadow-slate-200">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
