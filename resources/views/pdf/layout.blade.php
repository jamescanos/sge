@php
    // Color de marca de la identidad corporativa (DomPDF no entiende variables CSS)
    $color = $branding->primary_color;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>@yield('title')</title>
    <style>
        /* DomPDF NO entiende Tailwind ni flex/grid: se usa CSS clásico y tablas */
        @page { margin: 120px 40px 70px 40px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #222; }
        header { position: fixed; top: -95px; left: 0; right: 0; height: 80px; }
        footer { position: fixed; bottom: -50px; left: 0; right: 0; height: 30px;
                 font-size: 9px; color: #777; text-align: center; border-top: 1px solid #ccc; padding-top: 5px; }
        .logo { height: 56px; width: 56px; }
        .company { font-size: 16px; font-weight: bold; color: {{ $color }}; }
        .muted { color: #666; }
        .doc-box { border: 2px solid {{ $color }}; padding: 6px 10px; text-align: center; }
        .doc-box .type { font-size: 10px; text-transform: uppercase; color: {{ $color }}; }
        .doc-box .number { font-size: 16px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        .info td { padding: 3px 4px; vertical-align: top; }
        .items { margin-top: 15px; }
        .items th { background: {{ $color }}; color: #fff; padding: 6px; font-size: 10px; text-align: left; }
        .items td { padding: 5px 6px; border-bottom: 1px solid #ddd; }
        .right { text-align: right !important; }
        .totals { width: 40%; margin-left: 60%; margin-top: 10px; }
        .totals td { padding: 4px 6px; }
        .totals .grand td { font-size: 13px; font-weight: bold; border-top: 2px solid {{ $color }}; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 8px; background: #eee; font-size: 10px; }
        .signatures { margin-top: 60px; }
        .signatures td { width: 50%; text-align: center; padding-top: 35px; }
        .signatures span { border-top: 1px solid #333; padding: 4px 30px 0 30px; }
    </style>
</head>
<body>
    <header>
        <table>
            <tr>
                @if ($branding->logoFilePath())
                    <td style="width: 66px;">
                        <img src="{{ $branding->logoFilePath() }}" class="logo" alt="Logo">
                    </td>
                @endif
                <td>
                    <div class="company">{{ $branding->legal_name }}</div>
                    <div class="muted">NIT {{ $branding->nit }}{{ $branding->city ? ' · '.$branding->city : '' }}</div>
                    <div class="muted">
                        {{ collect([$branding->address, $branding->phone ? 'Tel. '.$branding->phone : null, $branding->email])->filter()->join(' · ') }}
                    </div>
                </td>
                <td style="width: 35%;">
                    <div class="doc-box">
                        <div class="type">@yield('doc_type')</div>
                        <div class="number">@yield('doc_number')</div>
                    </div>
                </td>
            </tr>
        </table>
    </header>

    <footer>
        Documento generado por el ERP de {{ $branding->trade_name }} el {{ now()->format('d/m/Y H:i') }}
        @if ($branding->website)
            · {{ $branding->website }}
        @endif
    </footer>

    <main>
        @yield('content')
    </main>
</body>
</html>
