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
<div class="card mb-4">
    <div class="card-header"><h2 class="h6 mb-0">Detalle de la venta</h2></div>
    <div class="table-responsive"><table class="table table-bordered align-middle">
        <thead class="table-dark"><tr><th>Producto</th><th>Cantidad</th><th>Precio unitario</th><th>Subtotal</th><th>Acción</th></tr></thead>
        <tbody>
            @forelse ($carrito as $item)
                <tr>
                    <td>{{ $item['nombre_producto'] }}</td><td>{{ $item['cantidad'] }}</td>
                    <td class="text-nowrap">S/ {{ number_format((float) $item['precio_unitario'], 2) }}</td>
                    <td class="text-nowrap">S/ {{ number_format($item['cantidad'] * (float) $item['precio_unitario'], 2) }}</td>
                    <td><form action="{{ route('ventas.carrito.eliminar', $item['id_producto']) }}" method="POST" data-confirm="¿Eliminar este producto de la venta?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                    </form></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No hay productos agregados a la venta.</td></tr>
            @endforelse
        </tbody>
        @if (count($carrito) > 0)
            <tfoot><tr><th colspan="3" class="text-end">TOTAL ESTIMADO</th><td colspan="2" class="fw-bold">S/ {{ number_format($totalCarrito, 2) }}</td></tr></tfoot>
        @endif
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
                    <div class="col-md-4">
                        <label for="cliente_nombre" class="form-label">Nombre del cliente</label>
                        <input type="text" id="cliente_nombre" name="cliente_nombre" value="{{ old('cliente_nombre') }}" maxlength="100" class="form-control" placeholder="Opcional">
                    </div>
                    <div class="col-md-4">
                        <label for="cliente_documento" class="form-label">Documento</label>
                        <input type="text" id="cliente_documento" name="cliente_documento" value="{{ old('cliente_documento') }}" inputmode="numeric" pattern="(?:[0-9]{8}|[0-9]{11})" minlength="8" maxlength="11" data-digits-only class="form-control" placeholder="DNI o RUC, solo números">
                    </div>
                    <div class="col-md-4">
                        <label for="metodo_pago" class="form-label">Método de pago</label>
                        <select id="metodo_pago" name="metodo_pago" class="form-select" required>
                            <option value="">Seleccionar</option>
                            @foreach (['Efectivo', 'Yape', 'Plin', 'Tarjeta'] as $metodo)
                                <option value="{{ $metodo }}" @selected(old('metodo_pago') === $metodo)>{{ $metodo }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-success btn-lg">Confirmar venta</button>
            </form>
        </div>
    </div>
@endif
