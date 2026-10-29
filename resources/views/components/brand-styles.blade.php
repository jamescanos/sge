{{-- Variables CSS con el color de marca (las usan bg-brand, text-brand, teal-*... y los gráficos) --}}
<style>
    :root {
        --brand: {{ $branding->brandRgb() }};
        --brand-dark: {{ $branding->brandRgb(-0.25) }};
        --brand-light: {{ $branding->brandRgb(0.9) }};
@foreach ($branding->brandScale() as $shade => $rgb)
        --brand-{{ $shade }}: {{ $rgb }};
@endforeach
    }
</style>
