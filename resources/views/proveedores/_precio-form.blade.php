<form action="{{ route('proveedores.precios.store') }}" method="POST">
    @csrf
    <input type="hidden" name="_form" value="precio-nuevo">
    <div class="row g-3 align-items-end">
        <div class="col-md-4">
            <label for="precio-producto" class="form-label">Producto</label>
            <select id="precio-producto" name="id_producto" class="form-select" required>
                <option value="">Seleccionar producto</option>
                @foreach ($productos as $producto)
                    <option value="{{ $producto->id_producto }}" @selected(old('id_producto') == $producto->id_producto)>{{ $producto->nombre_producto }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label for="precio-proveedor" class="form-label">Proveedor</label>
            <select id="precio-proveedor" name="id_proveedor" class="form-select" required>
                <option value="">Seleccionar proveedor</option>
                @foreach ($proveedores as $proveedor)
                    <option value="{{ $proveedor->id_proveedor }}" @selected(old('id_proveedor') == $proveedor->id_proveedor)>{{ $proveedor->nombre_empresa }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="precio-compra" class="form-label">Precio (S/)</label>
            <input id="precio-compra" type="number" name="precio_compra" value="{{ old('_form') === 'precio-nuevo' ? old('precio_compra') : '' }}" class="form-control" min="0" max="99999999.99" step="0.01" required>
        </div>
        <div class="col-md-2"><button class="btn btn-primary w-100" type="submit" @disabled($productos->isEmpty() || $proveedores->isEmpty())>Guardar precio</button></div>
    </div>
    @if ($productos->isEmpty() || $proveedores->isEmpty())
        <p class="text-muted mt-3 mb-0">Registra al menos un <a href="{{ route('productos.create') }}">producto</a> y un <a href="{{ route('proveedores.index') }}">proveedor</a> para asignar precios.</p>
    @endif
</form>
