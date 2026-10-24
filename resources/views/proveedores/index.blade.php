@extends('layouts.app')

@section('title', 'Proveedores')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <h1 class="h3 mb-0">Gestión de proveedores</h1>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary rounded-3 px-3 py-2" data-bs-toggle="offcanvas" data-bs-target="#supplier-create-panel" aria-controls="supplier-create-panel">+ Nuevo proveedor</button>
        <button type="button" class="btn btn-primary rounded-3 px-3 py-2" data-bs-toggle="offcanvas" data-bs-target="#supplier-price-panel" aria-controls="supplier-price-panel">Registrar precio</button>
        <a href="{{ route('proveedores.asignar-producto') }}" class="btn btn-outline-secondary rounded-3 px-3 py-2">Asignar producto</a>
    </div>
</div>
<div class="card data-table-card mb-4" data-live-region="suppliers">
    <div class="card-header list-card-header">
        <h2 class="h6 mb-0">Proveedores registrados</h2>
        <div class="list-search-wrap" data-list-search>
            <label class="visually-hidden" for="filter-suppliers">Buscar proveedores</label>
            <div class="list-search-field"><i class="bi bi-search" aria-hidden="true"></i><input id="filter-suppliers" type="search" placeholder="Buscar proveedores..." autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="suggestions-suppliers" data-list-search-input></div>
            <ul class="list-search-suggestions" id="suggestions-suppliers" role="listbox" hidden></ul>
            <span class="list-search-count" data-search-count></span>
        </div>
    </div>
    <div class="table-responsive"><table class="table table-hover data-table align-middle">
        <thead class="data-table-head"><tr><th>ID</th><th>Empresa</th><th class="text-end">Acciones</th></tr></thead>
        <tbody>
            @forelse ($proveedores as $proveedor)
                <tr data-search-row data-search-value="{{ $proveedor->id_proveedor }} {{ $proveedor->nombre_empresa }}" data-suggestion-label="{{ $proveedor->nombre_empresa }} · #{{ $proveedor->id_proveedor }}">
                    <td class="data-table-secondary">#{{ $proveedor->id_proveedor }}</td><td class="data-table-primary">{{ $proveedor->nombre_empresa }}</td>
                    <td><div class="action-buttons">
                        <form action="{{ route('proveedores.update', $proveedor) }}" method="POST" class="d-flex flex-wrap gap-2">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="_form" value="proveedor-{{ $proveedor->id_proveedor }}">
                            <label class="visually-hidden" for="empresa-{{ $proveedor->id_proveedor }}">Nombre de {{ $proveedor->nombre_empresa }}</label>
                            <input id="empresa-{{ $proveedor->id_proveedor }}" type="text" name="nombre_empresa" class="form-control inline-name" maxlength="100" value="{{ old('_form') === 'proveedor-'.$proveedor->id_proveedor ? old('nombre_empresa') : $proveedor->nombre_empresa }}" required>
                            <button type="submit" class="btn-icon btn-icon-success" aria-label="Guardar cambios de {{ $proveedor->nombre_empresa }}" title="Guardar cambios"><i class="bi bi-check-lg" aria-hidden="true"></i></button>
                        </form>
                        <form action="{{ route('proveedores.destroy', $proveedor) }}" method="POST" data-confirm="¿Eliminar este proveedor y sus precios?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-icon btn-icon-danger" aria-label="Eliminar {{ $proveedor->nombre_empresa }}" title="Eliminar"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                        </form>
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="3" class="table-empty-cell">@include('components.empty-state', [
                    'title' => 'Todavía no tienes proveedores',
                    'description' => 'Registra tus proveedores para relacionarlos con productos y precios de compra.',
                    'actionUrl' => '#supplier-create-panel',
                    'actionLabel' => 'Registrar proveedor',
                    'actionTarget' => 'supplier-create-panel',
                    'actionTargetType' => 'offcanvas',
                ])</td></tr>
            @endforelse
            <tr data-search-empty hidden><td colspan="3" class="text-center text-muted py-4">No encontramos proveedores que coincidan.</td></tr>
        </tbody>
    </table></div>
</div>
<div class="card data-table-card" data-live-region="supplier-prices">
    <div class="card-header list-card-header">
        <h2 class="h6 mb-0">Precios por proveedor</h2>
        <div class="list-search-wrap" data-list-search>
            <label class="visually-hidden" for="filter-supplier-prices">Buscar precios</label>
            <div class="list-search-field"><i class="bi bi-search" aria-hidden="true"></i><input id="filter-supplier-prices" type="search" placeholder="Buscar producto o proveedor..." autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="suggestions-supplier-prices" data-list-search-input></div>
            <ul class="list-search-suggestions" id="suggestions-supplier-prices" role="listbox" hidden></ul>
            <span class="list-search-count" data-search-count></span>
        </div>
    </div>
    <div class="table-responsive"><table class="table table-hover data-table align-middle">
        <thead class="data-table-head"><tr><th>Producto</th><th>Proveedor</th><th>Precio de compra</th><th class="text-end">Acciones</th></tr></thead>
        <tbody>
            @forelse ($precios as $precio)
                <tr data-search-row data-search-value="{{ $precio->producto->nombre_producto }} {{ $precio->proveedor->nombre_empresa }} {{ $precio->precio_compra }}" data-suggestion-label="{{ $precio->producto->nombre_producto }} · {{ $precio->proveedor->nombre_empresa }}">
                    <td class="data-table-primary">{{ $precio->producto->nombre_producto }}</td><td>{{ $precio->proveedor->nombre_empresa }}</td>
                    <td class="data-table-number">S/ {{ number_format((float) $precio->precio_compra, 2) }}</td>
                    <td><div class="action-buttons">
                        <form action="{{ route('proveedores.precios.update', [$precio->id_proveedor, $precio->id_producto]) }}" method="POST" class="d-flex flex-wrap gap-2">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="_form" value="precio-{{ $precio->id_producto }}-{{ $precio->id_proveedor }}">
                            <label class="visually-hidden" for="precio-{{ $precio->id_producto }}-{{ $precio->id_proveedor }}">Precio de compra de {{ $precio->producto->nombre_producto }} a {{ $precio->proveedor->nombre_empresa }}</label>
                            <input id="precio-{{ $precio->id_producto }}-{{ $precio->id_proveedor }}" type="number" name="precio_compra" class="form-control inline-price" min="0" max="99999999.99" step="0.01" value="{{ old('_form') === 'precio-'.$precio->id_producto.'-'.$precio->id_proveedor ? old('precio_compra') : $precio->precio_compra }}" required>
                            <button type="submit" class="btn-icon btn-icon-success" aria-label="Actualizar precio de {{ $precio->producto->nombre_producto }}" title="Guardar precio"><i class="bi bi-check-lg" aria-hidden="true"></i></button>
                        </form>
                        <form action="{{ route('proveedores.precios.destroy', [$precio->id_proveedor, $precio->id_producto]) }}" method="POST" data-confirm="¿Eliminar este precio?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-icon btn-icon-danger" aria-label="Eliminar precio de {{ $precio->producto->nombre_producto }}" title="Eliminar"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                        </form>
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="4" class="table-empty-cell">@include('components.empty-state', [
                    'title' => 'Aún no hay precios asignados',
                    'description' => 'Asigna un producto y proveedor para empezar a comparar costos.',
                    'actionUrl' => route('proveedores.asignar-producto'),
                    'actionLabel' => 'Asignar producto',
                ])</td></tr>
            @endforelse
            <tr data-search-empty hidden><td colspan="4" class="text-center text-muted py-4">No encontramos precios que coincidan.</td></tr>
        </tbody>
    </table></div>
</div>
<div class="offcanvas offcanvas-end admin-form-panel" tabindex="-1" id="supplier-create-panel" aria-labelledby="supplier-create-title">
    <div class="offcanvas-header border-bottom">
        <div><p class="dashboard-panel-kicker mb-1">PROVEEDORES</p><h2 class="offcanvas-title h5 fw-bold" id="supplier-create-title">Nuevo proveedor</h2></div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body">
        <form action="{{ route('proveedores.store') }}" method="POST">
            @csrf
            <input type="hidden" name="_form" value="proveedor-nuevo">
            <label for="nombre_empresa" class="form-label">Nombre de la empresa</label>
            <input id="nombre_empresa" type="text" name="nombre_empresa" value="{{ old('_form') === 'proveedor-nuevo' ? old('nombre_empresa') : '' }}" class="form-control mb-3" maxlength="100" placeholder="Ej. Distribuidora Majo S.A.C." required>
            <button type="submit" class="btn btn-primary rounded-3 px-4 py-2 w-100">Registrar proveedor</button>
        </form>
    </div>
</div>
<div class="offcanvas offcanvas-end admin-form-panel" tabindex="-1" id="supplier-price-panel" aria-labelledby="supplier-price-title">
    <div class="offcanvas-header border-bottom">
        <div><p class="dashboard-panel-kicker mb-1">COSTOS</p><h2 class="offcanvas-title h5 fw-bold" id="supplier-price-title">Precio de proveedor</h2></div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body">
        @include('proveedores._precio-form', compact('productos', 'proveedores'))
    </div>
</div>
@endsection

@push('scripts')
@if ($errors->any() && old('_form') === 'proveedor-nuevo')
    <script>document.addEventListener('DOMContentLoaded', () => window.appFeedbackReady.then(() => bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('supplier-create-panel')).show()));</script>
@elseif ($errors->any() && old('_form') === 'precio-nuevo')
    <script>document.addEventListener('DOMContentLoaded', () => window.appFeedbackReady.then(() => bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('supplier-price-panel')).show()));</script>
@endif
@endpush
