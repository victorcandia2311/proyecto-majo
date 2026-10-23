
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura {{ $venta->id_venta }}</title>

    <style>
        @page {
            size: A4;
            margin: 35px 40px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111111;
            margin: 0;
            padding: 0;
        }

        .azul {
            color: #222e60;
        }

        .rojo {
            color: #d93636;
        }

        /* Encabezado */
        .encabezado {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .encabezado td {
            border: none;
            vertical-align: top;
            padding: 0;
        }

        .titulo {
            font-size: 43px;
            font-weight: bold;
            color: #222e60;
            margin: 0 0 12px;
            letter-spacing: -1px;
        }

        .empresa {
            font-size: 11px;
            line-height: 1.7;
        }

        .logo {
            width: 85px;
            height: 85px;
            border-radius: 50%;
            background-color: #a4a4a8;
            color: #ffffff;
            text-align: center;
            vertical-align: middle;
            font-size: 17px;
            font-weight: bold;
            line-height: 85px;
            margin-left: auto;
        }

        /* Datos de facturación */
        .datos {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 35px;
        }

        .datos td {
            border: none;
            padding: 2px 0;
            vertical-align: top;
        }

        .datos .columna {
            width: 32%;
            line-height: 1.6;
        }

        .datos .columna-final {
            width: 36%;
        }

        .subtitulo {
            font-size: 15px;
            font-weight: bold;
            color: #222e60;
            margin: 0 0 7px;
        }

        .etiqueta {
            font-size: 14px;
            font-weight: bold;
            color: #222e60;
            white-space: nowrap;
        }

        .valor {
            text-align: right;
            white-space: nowrap;
        }

        /* Tabla de productos */
        .productos {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 10px;
        }

        .productos thead {
            display: table-header-group;
        }

        .productos th {
            color: #222e60;
            font-size: 13px;
            font-weight: bold;
            text-align: left;
            padding: 9px 7px;
            border-top: 1px solid #d93636;
            border-bottom: 1px solid #d93636;
        }

        .productos th:first-child,
        .productos td:first-child {
            text-align: center;
        }

        .productos td {
            padding: 10px 7px;
            border: none;
            vertical-align: top;
            overflow-wrap: break-word;
        }

        .productos .numero {
            text-align: right;
        }

        .productos tr {
            page-break-inside: avoid;
        }

        /* Totales */
        .resumen {
            width: 42%;
            margin-left: auto;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .resumen td {
            border: none;
            padding: 7px 5px;
        }

        .resumen .importe {
            text-align: right;
            white-space: nowrap;
        }

        .resumen .total td {
            color: #222e60;
            font-size: 18px;
            font-weight: bold;
            padding-top: 12px;
        }

        /* Firma y agradecimiento */
        .firma {
            text-align: right;
            margin-top: 30px;
            padding-right: 15px;
            color: #222e60;
            font-size: 12px;
        }

        .linea-firma {
            display: inline-block;
            width: 170px;
            border-bottom: 1px solid #999999;
            margin-bottom: 5px;
        }

        .pie {
            width: 100%;
            margin-top: 125px;
            border-collapse: collapse;
        }

        .pie td {
            border: none;
            padding: 0;
            vertical-align: bottom;
        }

        .gracias {
            color: #222e60;
            font-size: 28px;
            font-weight: bold;
            font-style: italic;
            text-align: right;
            padding-right: 12px !important;
            white-space: nowrap;
        }

        .condiciones {
            border-left: 2px solid #222e60 !important;
            padding-left: 12px !important;
            line-height: 1.8;
        }

        .condiciones-titulo {
            color: #d93636;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .condiciones p {
            margin: 6px 0;
        }

        .sin-datos {
            color: #666666;
        }
    </style>
</head>

<body>

    {{-- ENCABEZADO --}}
    <table class="encabezado">
        <tr>
            <td style="width: 75%;">
                <h1 class="titulo">FACTURA</h1>

                <div class="empresa">
                    <strong>MAJO - COVISIAN GROUP</strong><br>
                    Sistema de ventas e inventario<br>
                    Perú
                </div>
            </td>

            <td style="width: 25%; text-align: right;">
                <div class="logo">LOGO</div>
            </td>
        </tr>
    </table>

    {{-- DATOS DE CLIENTE Y VENTA --}}
    <table class="datos">
        <tr>
            <td class="columna">
                <div class="subtitulo">FACTURAR A</div>

                {{ $venta->cliente_nombre ?? 'Cliente general' }}<br>
                Documento: {{ $venta->cliente_documento ?? '-' }}
            </td>

            <td class="columna">
                <div class="subtitulo">ENVIAR A</div>

                {{ $venta->cliente_nombre ?? 'Cliente general' }}<br>
                {{ $venta->cliente_documento ?? '-' }}
            </td>

            <td class="columna-final">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td class="etiqueta">N.º DE FACTURA</td>
                        <td class="valor">{{ $venta->id_venta }}</td>
                    </tr>

                    <tr>
                        <td class="etiqueta">FECHA</td>
                        <td class="valor">
                            {{ $venta->fecha_hora->format('d/m/Y') }}
                        </td>
                    </tr>

                    <tr>
                        <td class="etiqueta">HORA</td>
                        <td class="valor">
                            {{ $venta->fecha_hora->format('H:i') }}
                        </td>
                    </tr>

                    <tr>
                        <td class="etiqueta">MÉTODO DE PAGO</td>
                        <td class="valor">{{ $venta->metodo_pago }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- DETALLE DE PRODUCTOS --}}
    <table class="productos">
        <thead>
            <tr>
                <th style="width: 12%;">CANT.</th>
                <th style="width: 43%;">DESCRIPCIÓN</th>
                <th style="width: 23%; text-align: right;">
                    PRECIO UNITARIO
                </th>
                <th style="width: 22%; text-align: right;">
                    IMPORTE
                </th>
            </tr>
        </thead>

        <tbody>
            @forelse ($detalles as $detalle)
                <tr>
                    <td>
                        {{ $detalle->cantidad }}
                    </td>

                    <td>
                        {{ $detalle->producto?->nombre_producto
                            ?? 'Producto eliminado' }}
                    </td>

                    <td class="numero">
                        {{ number_format(
                            (float) $detalle->precio_unitario, 2
                        ) }}
                    </td>

                    <td class="numero">
                        {{ number_format(
                            $detalle->cantidad *
                            (float) $detalle->precio_unitario, 2
                        ) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="sin-datos">
                        No hay productos registrados en esta venta.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- RESUMEN DE LA VENTA --}}
    <table class="resumen">
        <tr>
            <td>Subtotal</td>
            <td class="importe">
                S/ {{ number_format(
                    (float) $venta->total_venta, 2
                ) }}
            </td>
        </tr>

        <tr>
            <td>IGV</td>
            <td class="importe">S/ 0.00</td>
        </tr>

        <tr class="total">
            <td>TOTAL</td>
            <td class="importe">
                S/ {{ number_format(
                    (float) $venta->total_venta, 2
                ) }}
            </td>
        </tr>
    </table>

    {{-- FIRMA --}}
    <div class="firma">
        <div class="linea-firma"></div><br>
        Firma del responsable
    </div>

    {{-- PIE DE FACTURA --}}
    <table class="pie">
        <tr>
            <td style="width: 48%;" class="gracias">
                Gracias
            </td>

            <td style="width: 52%;" class="condiciones">
                <div class="condiciones-titulo">
                    CONDICIONES Y FORMA DE PAGO
                </div>

                <p>
                    Método de pago:
                    {{ $venta->metodo_pago }}
                </p>

                <p>
                    Gracias por su preferencia.
                </p>

                <p>
                    MAJO - COVISIAN GROUP
                </p>
            </td>
        </tr>
    </table>

</body>
</html>
