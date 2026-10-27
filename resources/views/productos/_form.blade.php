<div class="row g-3">
    <div class="col-12"><label for="nombre_producto" class="form-label">Nombre del producto</label>
    <input type="text" id="nombre_producto" name="nombre_producto" class="form-control" value="{{ old('nombre_producto', $producto?->nombre_producto) }}" maxlength="150" required>
</div>
    <div class="col-12">
        <label for="descripcion" class="form-label">Descripción</label>
        <textarea id="descripcion" name="descripcion" class="form-control" rows="3" maxlength="2000">{{ old('descripcion', $producto?->descripcion) }}</textarea>
    </div>
    <div class="col-12">
        <label for="imagen" class="form-label">Imagen del producto</label>
        <input type="file" id="imagen" name="imagen" class="form-control" accept="image/jpeg,image/png,image/webp">
        <div class="form-text">JPG, PNG o WebP. Máximo 3 MB.</div>
        @if ($producto?->imagen_path)
            <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($producto->imagen_path) }}" alt="Imagen actual de {{ $producto->nombre_producto }}" class="mt-2 rounded" style="max-width: 140px; max-height: 120px; object-fit: cover;">
        @endif
    </div>
    @if ($producto)
        <div class="col-md-6"><label for="stock_actual" class="form-label">Stock actual</label>
            <input id="stock_actual" class="form-control" value="{{ $producto->stock_actual }}" disabled>
            <p class="form-text mb-0">El stock se modifica desde <a href="{{ route('inventario.index') }}">Inventario</a>.</p>
        </div>
    @else
        <div class="col-md-6"><label for="stock_actual" class="form-label">Stock inicial</label>
    <input type="number" id="stock_actual" name="stock_actual" class="form-control" value="{{ old('stock_actual', 0) }}" min="0" max="2147483647" step="1" required>
</div>
    @endif
    <div class="col-md-6"><label for="stock_minimo_alerta" class="form-label">Stock mínimo para alerta</label>
    <input type="number" id="stock_minimo_alerta" name="stock_minimo_alerta" class="form-control" value="{{ old('stock_minimo_alerta', $producto?->stock_minimo_alerta ?? 10) }}" min="0" max="2147483647" step="1" required>
</div>
    <div class="col-md-6"><label for="precio_venta" class="form-label">Precio de venta (S/)</label>
    <input type="number" id="precio_venta" name="precio_venta" class="form-control" value="{{ old('precio_venta', $producto?->precio_venta) }}" min="0" max="99999999.99" step="0.01" required>
</div>
</div>
<div class="d-flex flex-wrap gap-2 mt-4">
    <button class="btn btn-success" type="submit">{{ $producto ? 'Guardar cambios' : 'Guardar producto' }}</button>
    <a href="{{ route('productos.index') }}" class="btn btn-secondary">Cancelar</a>
</div>
