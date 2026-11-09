<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de ventas</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 28px;
        }

        body {
            color: #172033;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
        }

        h1 {
            color: #222e60;
            font-size: 22px;
            margin: 0 0 5px;
        }

        .periodo {
            color: #536176;
            font-size: 11px;
            margin: 0 0 18px;
        }

        .resumen {
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            border-collapse: collapse;
            margin-bottom: 14px;
            width: 100%;
        }

        .resumen td {
            color: #222e60;
            font-size: 11px;
            font-weight: bold;
            padding: 9px;
        }

        .resumen td:last-child {
            text-align: right;
        }

        .ventas {
            border-collapse: collapse;
            table-layout: fixed;
            width: 100%;
        }

        .ventas thead {
            display: table-header-group;
        }

        .ventas th {
            background: #f3f5f9;
            color: #536176;
            font-size: 8px;
            text-align: left;
            text-transform: uppercase;
        }

        .ventas th,
        .ventas td {
            border: 1px solid #dfe4ec;
            padding: 7px 5px;
        }

        .ventas tbody tr {
            page-break-inside: avoid;
        }

        .numero {
            text-align: right;
            white-space: nowrap;
        }

        .vacio {
            color: #68758a;
            padding: 18px !important;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Historial de ventas</h1>
    <p class="periodo">
        Periodo:
        @if ($fechaDesde && $fechaHasta)
            {{ $fechaDesde === $fechaHasta ? $fechaDesde : $fechaDesde.' al '.$fechaHasta }}
        @elseif ($fechaDesde)
            Desde {{ $fechaDesde }}
        @elseif ($fechaHasta)
            Hasta {{ $fechaHasta }}
        @else
            Todas las fechas
        @endif
    </p>

    <table class="resumen">
        <tr>
            <td>{{ $ventas->count() }} {{ Str::plural('venta', $ventas->count()) }}</td>
            <td>Total del periodo: S/ {{ number_format((float) $totalVentas, 2) }}</td>
        </tr>
    </table>

    <table class="ventas">
        <thead>
            <tr>
                <th style="width: 6%;">N.º</th>
                <th style="width: 12%;">Fecha</th>
                <th style="width: 17%;">Cliente</th>
                <th style="width: 12%;">Método de pago</th>
                <th style="width: 9%;">Descuento (%)</th>
                <th style="width: 10%;">Descuento</th>
                <th style="width: 12%;">Subtotal (sin IGV)</th>
                <th style="width: 9%;">IGV</th>
                <th style="width: 13%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($ventas as $venta)
                <tr>
                    <td>#{{ $venta->id_venta }}</td>
                    <td>{{ $venta->fechaHoraEnPeru()->format('d/m/Y H:i') }}</td>
                    <td>{{ $venta->cliente_nombre ?? 'Cliente general' }}</td>
                    <td>{{ $venta->metodo_pago }}</td>
                    <td class="numero">{{ $venta->descuento_porcentaje !== null ? number_format((float) $venta->descuento_porcentaje, 2).' %' : 'No registrado' }}</td>
                    <td class="numero">{{ $venta->descuento_monto !== null ? 'S/ '.number_format((float) $venta->descuento_monto, 2) : 'No registrado' }}</td>
                    <td class="numero">{{ $venta->subtotalNeto() !== null ? 'S/ '.number_format((float) $venta->subtotalNeto(), 2) : 'No registrado' }}</td>
                    <td class="numero">{{ $venta->igv_monto !== null ? 'S/ '.number_format((float) $venta->igv_monto, 2) : 'No registrado' }}</td>
                    <td class="numero">S/ {{ number_format((float) $venta->total_venta, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="vacio">No se registraron ventas en el periodo seleccionado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
