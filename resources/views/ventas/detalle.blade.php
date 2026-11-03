@extends('layouts.app')

@section('title', 'Detalle de venta')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <h1 class="h3 mb-0">Venta #{{ $venta->id_venta }}</h1>
    <div class="d-flex flex-wrap gap-2">
        <button
            type="button"
            class="btn btn-danger"
            id="print-ticket-button"
            data-print-url="{{ route('ventas.pdf', ['venta' => $venta, 'inline' => true]) }}"
        >
            <i class="bi bi-printer me-1" aria-hidden="true"></i>Imprimir Ticket
        </button>
        <a href="{{ route('ventas.index') }}" class="btn btn-secondary">Volver a ventas</a>
    </div>
</div>
<div class="card mb-4"><div class="card-body">
    <dl class="row mb-0">
        <div class="col-md-3"><dt>Fecha</dt><dd>{{ $venta->fechaHoraEnPeru()->format('d/m/Y H:i:s') }}</dd></div>
        <div class="col-md-3"><dt>Cliente</dt><dd>{{ $venta->cliente_nombre ?? 'Cliente general' }}</dd></div>
        <div class="col-md-3"><dt>Documento</dt><dd>{{ $venta->cliente_documento ?? '—' }}</dd></div>
        <div class="col-md-3"><dt>Método de pago</dt><dd>{{ $venta->metodo_pago }}</dd></div>
    </dl>
</div></div>
<div class="card data-table-card">
    <div class="card-header"><h2 class="h6 mb-0">Productos vendidos</h2></div>
    <div class="table-responsive"><table class="table data-table align-middle">
        <thead class="data-table-head"><tr><th>Producto</th><th>Cantidad</th><th>Precio unitario</th><th>Subtotal</th></tr></thead>
        <tbody>
            @forelse ($detalles as $detalle)
                <tr>
                    <td class="data-table-primary">{{ $detalle->producto?->nombre_producto ?? 'Producto eliminado' }}</td><td class="data-table-number">{{ $detalle->cantidad }}</td>
                    <td class="data-table-number">S/ {{ number_format((float) $detalle->precio_unitario, 2) }}</td>
                    <td class="data-table-number">S/ {{ number_format($detalle->cantidad * (float) $detalle->precio_unitario, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">Esta venta no tiene detalles registrados.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            @if ($venta->subtotal_venta !== null)
                <tr><th colspan="3" class="text-end">SUBTOTAL</th><th class="data-table-number">S/ {{ number_format((float) $venta->subtotal_venta, 2) }}</th></tr>
                <tr><th colspan="3" class="text-end">Descuento ({{ number_format((float) $venta->descuento_porcentaje, 2) }} %)</th><th class="data-table-number">S/ {{ number_format((float) $venta->descuento_monto, 2) }}</th></tr>
                <tr><th colspan="3" class="text-end">IGV ({{ number_format((float) $venta->igv_porcentaje, 2) }} %)</th><th class="data-table-number">S/ {{ number_format((float) $venta->igv_monto, 2) }}</th></tr>
            @else
                <tr><th colspan="3" class="text-end">Importe original registrado</th><th class="data-table-number">S/ {{ number_format((float) $venta->total_venta, 2) }}</th></tr>
                <tr><th colspan="3" class="text-end">IGV / descuento</th><th class="data-table-number">No registrado</th></tr>
            @endif
            <tr><th colspan="3" class="text-end">TOTAL FINAL</th><th class="data-table-number">S/ {{ number_format((float) $venta->total_venta, 2) }}</th></tr>
        </tfoot>
    </table></div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const printButton = document.getElementById('print-ticket-button');

    if (!printButton) {
        return;
    }

    printButton.addEventListener('click', function () {
        const printFrame = document.createElement('iframe');
        printFrame.title = 'Comprobante de venta para imprimir';
        printFrame.tabIndex = -1;
        printFrame.setAttribute('aria-hidden', 'true');
        printFrame.style.cssText = 'position:fixed;right:0;bottom:0;width:1px;height:1px;border:0;opacity:0;pointer-events:none;';

        const cleanupTimer = window.setTimeout(function () {
            printFrame.remove();
        }, 60000);

        printFrame.addEventListener('load', function onPrintDocumentLoad() {
            printFrame.removeEventListener('load', onPrintDocumentLoad);

            try {
                const printWindow = printFrame.contentWindow;

                if (!printWindow) {
                    throw new Error('No se pudo acceder al documento del comprobante.');
                }

                printWindow.addEventListener('afterprint', function () {
                    window.clearTimeout(cleanupTimer);
                    printFrame.remove();
                }, { once: true });

                printWindow.focus();
                window.setTimeout(function () {
                    try {
                        printWindow.print();
                    } catch (error) {
                        window.clearTimeout(cleanupTimer);
                        printFrame.remove();
                        console.error('No se pudo abrir el diálogo de impresión.', error);
                        window.alert('No se pudo abrir el diálogo de impresión. Inténtalo nuevamente.');
                    }
                }, 300);
            } catch (error) {
                window.clearTimeout(cleanupTimer);
                printFrame.remove();
                console.error('No se pudo abrir el diálogo de impresión.', error);
                window.alert('No se pudo abrir el diálogo de impresión. Inténtalo nuevamente.');
            }
        }, { once: true });

        printFrame.src = printButton.dataset.printUrl;
        document.body.appendChild(printFrame);
    });
});
</script>
@endpush
