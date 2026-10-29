@extends('pdf.layout')

@section('title', 'Factura '.$sale->invoice_number)
@section('doc_type', 'Factura de venta')
@section('doc_number', $sale->invoice_number)

@section('content')
    <table class="info">
        <tr>
            <td style="width: 55%;">
                <strong>Cliente:</strong> {{ $sale->client?->name }}<br>
                <strong>{{ $sale->client?->document_type }}:</strong> {{ $sale->client?->document_number }}<br>
                <strong>Dirección:</strong> {{ $sale->client?->address ?? '—' }}<br>
                <strong>Teléfono:</strong> {{ $sale->client?->phone ?? '—' }}
            </td>
            <td style="width: 45%;">
                <strong>Fecha:</strong> {{ $sale->sale_date->format('d/m/Y') }}<br>
                <strong>Estado:</strong> <span class="badge">{{ ucfirst($sale->status) }}</span><br>
                <strong>Vendedor:</strong> {{ $sale->user?->name }}
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>#</th>
                <th>Producto</th>
                <th class="right">Cantidad</th>
                <th class="right">Precio unit.</th>
                <th class="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->details as $i => $detail)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $detail->product?->name }}</td>
                    <td class="right">{{ $detail->quantity }}</td>
                    <td class="right">${{ number_format((float) $detail->unit_price, 2) }}</td>
                    <td class="right">${{ number_format((float) $detail->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="right">${{ number_format((float) $sale->subtotal, 2) }}</td></tr>
        <tr><td>IVA (19%)</td><td class="right">${{ number_format((float) $sale->tax, 2) }}</td></tr>
        <tr class="grand"><td>TOTAL</td><td class="right">${{ number_format((float) $sale->total, 2) }}</td></tr>
    </table>

    @if ($sale->notes)
        <p><strong>Notas:</strong> {{ $sale->notes }}</p>
    @endif

    <p class="muted" style="margin-top: 30px;">
        Documento académico sin validez fiscal. En un ERP real, la factura electrónica en Colombia se
        transmite a la DIAN (UBL 2.1, CUFE y código QR).
    </p>
@endsection
