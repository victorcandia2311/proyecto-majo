<div class="card mb-4">
    <div class="card-header"><h2 class="h6 mb-0">Agregar producto a la venta</h2></div>
    <div class="card-body">
        <form action="{{ route('ventas.carrito.agregar') }}" method="POST" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-6">
                <label for="venta-producto" class="form-label">Producto</label>
                <select id="venta-producto" name="id_producto" class="form-select" required>
                    <option value="">Seleccionar producto</option>
                    @foreach ($productos as $producto)
                        <option value="{{ $producto->id_producto }}" @selected(old('id_producto') == $producto->id_producto)>{{ $producto->nombre_producto }} — Stock: {{ $producto->stock_actual }} — S/ {{ number_format((float) $producto->precio_venta, 2) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="venta-cantidad" class="form-label">Cantidad</label>
                <input id="venta-cantidad" name="cantidad" value="{{ old('cantidad', 1) }}" type="number" min="1" max="2147483647" step="1" class="form-control" required>
            </div>
            <div class="col-md-3"><button type="submit" class="btn btn-primary w-100" @disabled($productos->isEmpty())>+ Agregar</button></div>
        </form>
        @if ($productos->isEmpty())
            <p class="text-muted mt-3 mb-0">No hay productos con stock disponible.
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('inventario.index') }}">Revisar inventario</a>.
                @else
                    Solicita el reabastecimiento al administrador.
                @endif
            </p>
        @endif
    </div>
</div>
<div class="card data-table-card mb-4">
    <div class="card-header"><h2 class="h6 mb-0">Detalle de la venta</h2></div>
    <div class="table-responsive"><table class="table data-table align-middle">
        <thead class="data-table-head"><tr><th>Producto</th><th>Cantidad</th><th>Precio unitario</th><th>Subtotal</th><th class="text-end">Acción</th></tr></thead>
        <tbody>
            @forelse ($carrito as $item)
                <tr>
                    <td class="data-table-primary">{{ $item['nombre_producto'] }}</td><td class="data-table-number">{{ $item['cantidad'] }}</td>
                    <td class="data-table-number">S/ {{ number_format((float) $item['precio_unitario'], 2) }}</td>
                    <td class="data-table-number">S/ {{ number_format($item['cantidad'] * (float) $item['precio_unitario'], 2) }}</td>
                    <td><form action="{{ route('ventas.carrito.eliminar', $item['id_producto']) }}" method="POST" data-confirm="¿Eliminar este producto de la venta?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-icon btn-icon-danger" aria-label="Quitar {{ $item['nombre_producto'] }} de la venta" title="Quitar producto"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                    </form></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No hay productos agregados a la venta.</td></tr>
            @endforelse
        </tbody>
    </table></div>
    @if (count($carrito) > 0)
        <div class="card-body">
            <p class="text-muted">El precio y la disponibilidad se verifican al confirmar la venta.</p>
            <form action="{{ route('ventas.carrito.vaciar') }}" method="POST" data-confirm="¿Vaciar toda la venta?">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-secondary">Vaciar venta</button>
            </form>
        </div>
    @endif
</div>
@if (count($carrito) > 0)
    <div class="card mb-4">
        <div class="card-header"><h2 class="h6 mb-0">Datos de facturación</h2></div>
        <div class="card-body">
            <form action="{{ route('ventas.confirmar') }}" method="POST">
                @csrf
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label for="cliente_nombre" class="form-label">Nombre del cliente</label>
                        <input type="text" id="cliente_nombre" name="cliente_nombre" value="{{ old('cliente_nombre') }}" maxlength="100" class="form-control" placeholder="Opcional">
                    </div>
                    <div class="col-md-3">
                        <label for="cliente_documento" class="form-label">Documento</label>
                        <input type="text" id="cliente_documento" name="cliente_documento" value="{{ old('cliente_documento') }}" inputmode="numeric" pattern="(?:[0-9]{8}|[0-9]{11})" minlength="8" maxlength="11" data-digits-only class="form-control" placeholder="DNI o RUC, solo números">
                    </div>
                    <div class="col-md-3">
                        <label for="metodo_pago" class="form-label">Método de pago</label>
                        <select id="metodo_pago" name="metodo_pago" class="form-select" required>
                            <option value="">Seleccionar</option>
                            @foreach (['Efectivo', 'Yape', 'Plin', 'Tarjeta'] as $metodo)
                                <option value="{{ $metodo }}" @selected(old('metodo_pago') === $metodo)>{{ $metodo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="descuento_porcentaje" class="form-label">Descuento (%)</label>
                        <div class="input-group">
                            <input
                                type="number"
                                id="descuento_porcentaje"
                                name="descuento_porcentaje"
                                value="{{ old('descuento_porcentaje', '0') }}"
                                min="0"
                                max="100"
                                step="0.01"
                                inputmode="decimal"
                                class="form-control @error('descuento_porcentaje') is-invalid @enderror"
                                aria-describedby="descuento-ayuda"
                                required
                            >
                            <span class="input-group-text">%</span>
                            @error('descuento_porcentaje')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div id="descuento-ayuda" class="form-text">De 0 a 100; se aplica sobre el subtotal.</div>
                    </div>
                </div>
                <div class="border-top pt-3 mb-4" data-sale-summary data-subtotal="{{ number_format($totalCarrito, 2, '.', '') }}">
                    <div class="d-flex justify-content-between mb-2"><span>Subtotal (IGV incluido)</span><strong id="sale-summary-subtotal">S/ {{ number_format($totalCarrito, 2) }}</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>IGV (18 %)</span><strong id="sale-summary-igv">S/ 0.00</strong></div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Descuento (<span id="sale-summary-discount-percentage">0.00</span> %)</span>
                        <strong id="sale-summary-discount-amount">S/ 0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between border-top pt-2 fs-5 fw-bold"><span>Total final</span><strong id="sale-summary-total">S/ 0.00</strong></div>
                </div>
                <button type="submit" class="btn btn-success btn-lg">Confirmar venta</button>
            </form>
        </div>
    </div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const summary = document.querySelector('[data-sale-summary]');
    const discountInput = document.getElementById('descuento_porcentaje');
    if (!summary || !discountInput) return;

    const currency = new Intl.NumberFormat('es-PE', { style: 'currency', currency: 'PEN' });
    const subtotalCents = Math.round(Number(summary.dataset.subtotal) * 100);
    const updateSummary = () => {
        const valid =
            discountInput.checkValidity() &&
            discountInput.value.trim() !== '' &&
            Number.isFinite(Number(discountInput.value));

        if (!valid) {
            document.getElementById('sale-summary-discount-percentage').textContent = '—';
            document.getElementById('sale-summary-igv').textContent = '—';
            document.getElementById('sale-summary-discount-amount').textContent = '—';
            document.getElementById('sale-summary-total').textContent = '—';
            return;
        }

        const percentageBasisPoints = Math.round(
            Number(discountInput.value) * 100
        );

        // Aplicar el descuento sobre el subtotal que ya incluye IGV.
        const discountCents = Math.floor(
            (subtotalCents * percentageBasisPoints + 5000) / 10000
        );

        // El total ya incluye IGV.
        const totalCents = subtotalCents - discountCents;

        // Extraer la base imponible.
        const taxableBaseCents = Math.floor(
            (totalCents * 100 + 59) / 118
        );

        // Extraer el IGV incluido.
        const igvCents = totalCents - taxableBaseCents;

        document.getElementById('sale-summary-discount-percentage').textContent =
            (percentageBasisPoints / 100).toFixed(2);

        document.getElementById('sale-summary-subtotal').textContent =
            formatCurrency(subtotalCents);

        document.getElementById('sale-summary-igv').textContent =
            formatCurrency(igvCents);

        document.getElementById('sale-summary-discount-amount').textContent =
            formatCurrency(discountCents);

        document.getElementById('sale-summary-total').textContent =
            formatCurrency(totalCents);
    };

    discountInput.addEventListener('input', updateSummary);
    updateSummary();
});
</script>
@endpush
