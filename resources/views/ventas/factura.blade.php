<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de venta {{ $venta->id_venta }}</title>
    <style>
        @page { margin: 35px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h1, h2 { text-align: center; }
        h1 { font-size: 24px; margin-bottom: 4px; }
        h2 { font-size: 14px; margin-bottom: 25px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; table-layout: fixed; }
        th, td { border: 1px solid #999; padding: 8px; word-wrap: break-word; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        th { background: #eee; }
        .number { text-align: right; }
        .total { font-size: 16px; text-align: right; margin-top: 20px; page-break-inside: avoid; }
        footer { text-align: center; margin-top: 30px; }
    </style>
</head>
<body>
    <h1>MAJO - COVISIAN GROUP</h1>
    <h2>RECIBO DE VENTA</h2>
    <p><strong>N.º de venta:</strong> {{ $venta->id_venta }}</p>
    <p><strong>Fecha:</strong> {{ $venta->fecha_hora->format('d/m/Y H:i:s') }}</p>
    <p><strong>Cliente:</strong> {{ $venta->cliente_nombre ?? 'Cliente general' }}</p>
    <p><strong>Documento:</strong> {{ $venta->cliente_documento ?? '-' }}</p>
    <p><strong>Método de pago:</strong> {{ $venta->metodo_pago }}</p>
    <table>
        <thead>
            <tr><th style="width: 40%">Producto</th><th>Cantidad</th><th>Precio unitario</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
            @foreach ($detalles as $detalle)
                <tr>
                    <td>{{ $detalle->producto->nombre_producto }}</td>
                    <td class="number">{{ $detalle->cantidad }}</td>
                    <td class="number">S/ {{ number_format((float) $detalle->precio_unitario, 2) }}</td>
                    <td class="number">S/ {{ number_format($detalle->cantidad * (float) $detalle->precio_unitario, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p class="total"><strong>TOTAL: S/ {{ number_format((float) $venta->total_venta, 2) }}</strong></p>
    <footer>Gracias por su compra.</footer>
</body>
</html>
