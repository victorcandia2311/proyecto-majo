@extends('layouts.app')

@section('title', 'Proveedores')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <h1 class="h3 mb-0">Gestión de proveedores</h1>
    <a href="{{ route('proveedores.asignar-producto') }}" class="btn btn-primary">Asignar producto</a>
</div>
<div class="card mb-4">
    <div class="card-header"><h2 class="h6 mb-0">Registrar proveedor</h2></div>
    <div class="card-body">
        <form action="{{ route('proveedores.store') }}" method="POST" class="row g-3 align-items-end">
            @csrf
            <input type="hidden" name="_form" value="proveedor-nuevo">
            <div class="col-md-8">
                <label for="nombre_empresa" class="form-label">Nombre de la empresa</label>
                <input id="nombre_empresa" type="text" name="nombre_empresa" value="{{ old('_form') === 'proveedor-nuevo' ? old('nombre_empresa') : '' }}" class="form-control" maxlength="100" placeholder="Ej. Distribuidora Majo S.A.C." required>
            </div>
            <div class="col-md-4"><button type="submit" class="btn btn-success w-100">+ Registrar proveedor</button></div>
        </form>
    </div>
</div>
<div class="card mb-4">
    <div class="card-header"><h2 class="h6 mb-0">Proveedores registrados</h2></div>
    <div class="table-responsive"><table class="table table-bordered table-hover align-middle">
        <thead class="table-dark"><tr><th>ID</th><th>Empresa</th><th>Acciones</th></tr></thead>
        <tbody>
            @forelse ($proveedores as $proveedor)
                <tr>
                    <td>{{ $proveedor->id_proveedor }}</td><td>{{ $proveedor->nombre_empresa }}</td>
                    <td><div class="action-buttons">
                        <form action="{{ route('proveedores.update', $proveedor) }}" method="POST" class="d-flex flex-wrap gap-2">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="_form" value="proveedor-{{ $proveedor->id_proveedor }}">
                            <label class="visually-hidden" for="empresa-{{ $proveedor->id_proveedor }}">Nombre de {{ $proveedor->nombre_empresa }}</label>
                            <input id="empresa-{{ $proveedor->id_proveedor }}" type="text" name="nombre_empresa" class="form-control inline-name" maxlength="100" value="{{ old('_form') === 'proveedor-'.$proveedor->id_proveedor ? old('nombre_empresa') : $proveedor->nombre_empresa }}" required>
                            <button type="submit" class="btn btn-warning btn-sm">Guardar cambios</button>
                        </form>
                        <form action="{{ route('proveedores.destroy', $proveedor) }}" method="POST" data-confirm="¿Eliminar este proveedor y sus precios?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                        </form>
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center text-muted py-4">No hay proveedores registrados.</td></tr>
            @endforelse
        </tbody>
    </table></div>
</div>
<div class="card mb-4">
    <div class="card-header"><h2 class="h6 mb-0">Registrar precio de proveedor</h2></div>
    <div class="card-body">@include('proveedores._precio-form', compact('productos', 'proveedores'))</div>
</div>
<div class="card">
    <div class="card-header"><h2 class="h6 mb-0">Precios por proveedor</h2></div>
    <div class="table-responsive"><table class="table table-bordered table-hover align-middle">
        <thead class="table-dark"><tr><th>Producto</th><th>Proveedor</th><th>Precio de compra</th><th>Acciones</th></tr></thead>
        <tbody>
            @forelse ($precios as $precio)
                <tr>
                    <td>{{ $precio->producto->nombre_producto }}</td><td>{{ $precio->proveedor->nombre_empresa }}</td>
                    <td class="text-nowrap">S/ {{ number_format((float) $precio->precio_compra, 2) }}</td>
                    <td><div class="action-buttons">
                        <form action="{{ route('proveedores.precios.update', [$precio->id_proveedor, $precio->id_producto]) }}" method="POST" class="d-flex flex-wrap gap-2">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="_form" value="precio-{{ $precio->id_producto }}-{{ $precio->id_proveedor }}">
                            <label class="visually-hidden" for="precio-{{ $precio->id_producto }}-{{ $precio->id_proveedor }}">Precio de compra de {{ $precio->producto->nombre_producto }} a {{ $precio->proveedor->nombre_empresa }}</label>
                            <input id="precio-{{ $precio->id_producto }}-{{ $precio->id_proveedor }}" type="number" name="precio_compra" class="form-control inline-price" min="0" max="99999999.99" step="0.01" value="{{ old('_form') === 'precio-'.$precio->id_producto.'-'.$precio->id_proveedor ? old('precio_compra') : $precio->precio_compra }}" required>
                            <button type="submit" class="btn btn-warning btn-sm">Actualizar</button>
                        </form>
                        <form action="{{ route('proveedores.precios.destroy', [$precio->id_proveedor, $precio->id_producto]) }}" method="POST" data-confirm="¿Eliminar este precio?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                        </form>
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">No hay precios de proveedores registrados.</td></tr>
            @endforelse
        </tbody>
    </table></div>
</div>
@endsection
