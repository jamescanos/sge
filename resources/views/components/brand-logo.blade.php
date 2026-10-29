{{--
    Logo de la organización: la imagen subida en Identidad corporativa o, si no hay,
    un cuadro con las iniciales del nombre comercial en el color de marca.
    Uso: <x-brand-logo class="h-10 w-10" />
--}}
@if ($branding->logoUrl())
    <img src="{{ $branding->logoUrl() }}" alt="Logo de {{ $branding->trade_name }}"
         {{ $attributes->merge(['class' => 'h-10 w-10 rounded-lg bg-white object-contain p-0.5']) }}>
@else
    <span {{ $attributes->merge(['class' => 'inline-flex h-10 w-10 items-center justify-center rounded-lg bg-brand text-sm font-bold text-white shadow-sm']) }}>
        {{ $branding->initials() }}
    </span>
@endif
