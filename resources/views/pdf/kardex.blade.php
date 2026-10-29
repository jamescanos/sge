@extends('pdf.layout')

@section('title', 'Kardex '.$product->name)
@section('doc_type', 'Kardex de inventario')
@section('doc_number', 'PROD-'.str_pad($product->id, 5, '0', STR_PAD_LEFT))

@section('content')
    <table class="info">
        <tr>
            <td style="width: 60%;">
                <strong>Producto:</strong> {{ $product->name }}<br>
                <strong>Stock actual:</strong> {{ $product->stock }}
            </td>
            <td style="width: 40%;">
                <strong>Periodo:</strong> {{ $from ?: 'inicio' }} a {{ $to ?: 'hoy' }}<br>
                <strong>Entradas:</strong> {{ $totals['entradas'] }} · <strong>Salidas:</strong> {{ $totals['salidas'] }}
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Concepto</th>
                <th>Documento</th>
                <th class="right">Entrada</th>
                <th class="right">Salida</th>
                <th class="right">Saldo</th>
                <th class="right">Valor unit.</th>
                <th>Usuario</th>
                <th>Notas</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($movements as $m)
                <tr>
                    <td>{{ $m->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $m->conceptLabel() }}</td>
                    <td>{{ $m->referenceLabel() ?? '—' }}</td>
                    <td class="right">{{ $m->type === 'entrada' ? $m->quantity : '' }}</td>
                    <td class="right">{{ $m->type === 'salida' ? $m->quantity : '' }}</td>
                    <td class="right"><strong>{{ $m->balance }}</strong></td>
                    <td class="right">{{ $m->unit_cost !== null ? '$'.number_format((float) $m->unit_cost, 2) : '' }}</td>
                    <td>{{ $m->user?->name ?? 'Sistema' }}</td>
                    <td>{{ $m->notes }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
