@extends('pdf.layout')

@section('title', 'Orden de compra '.$purchase->purchase_number)
@section('doc_type', 'Orden de compra')
@section('doc_number', $purchase->purchase_number)

@section('content')
    <table class="info">
        <tr>
            <td style="width: 55%;">
                <strong>Proveedor:</strong> {{ $purchase->provider?->name }}<br>
                <strong>{{ $purchase->provider?->document_type }}:</strong> {{ $purchase->provider?->document_number }}<br>
                <strong>Contacto:</strong> {{ $purchase->provider?->contact_name ?? '—' }}<br>
                <strong>Teléfono:</strong> {{ $purchase->provider?->phone ?? '—' }}
            </td>
            <td style="width: 45%;">
                <strong>Fecha:</strong> {{ $purchase->purchase_date->format('d/m/Y') }}<br>
                <strong>Estado:</strong> <span class="badge">{{ ucfirst($purchase->status) }}</span><br>
                <strong>Factura proveedor:</strong> {{ $purchase->provider_invoice ?? '—' }}<br>
                <strong>Registró:</strong> {{ $purchase->user?->name }}
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>#</th>
                <th>Producto</th>
                <th class="right">Pedida</th>
                <th class="right">Recibida</th>
                <th class="right">Costo unit.</th>
                <th class="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchase->details as $i => $detail)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $detail->product?->name }}</td>
                    <td class="right">{{ $detail->quantity }}</td>
                    <td class="right">{{ $detail->received_quantity }}</td>
                    <td class="right">${{ number_format((float) $detail->unit_cost, 2) }}</td>
                    <td class="right">${{ number_format((float) $detail->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="right">${{ number_format((float) $purchase->subtotal, 2) }}</td></tr>
        <tr><td>IVA (19%)</td><td class="right">${{ number_format((float) $purchase->tax, 2) }}</td></tr>
        <tr class="grand"><td>TOTAL</td><td class="right">${{ number_format((float) $purchase->total, 2) }}</td></tr>
    </table>

    @if ($purchase->notes)
        <p><strong>Notas:</strong> {{ $purchase->notes }}</p>
    @endif

    <table class="signatures">
        <tr>
            <td><span>Elaboró</span></td>
            <td><span>Recibió (almacén)</span></td>
        </tr>
    </table>
@endsection
