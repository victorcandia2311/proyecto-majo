@extends('layouts.app')

@section('title', 'Proveedores')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Gestión de proveedores</h1>
        <p class="text-muted mb-0">Administra tus proveedores y consulta los productos y precios de compra de cada uno.</p>
    </div>
    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#supplier-create-modal">
        <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Nuevo proveedor
    </button>
</div>

<div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
    <h2 class="h5 mb-0 me-auto">Proveedores registrados</h2>
</div>

<div class="card data-table-card" data-live-region="suppliers">
    <div class="card-header list-card-header">
        <h2 class="h6 mb-0">Proveedores</h2>
        <div class="list-search-wrap" data-list-search>
            <label class="visually-hidden" for="filter-suppliers">Buscar proveedores</label>
            <div class="list-search-field">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input
                    id="filter-suppliers"
                    type="search"
                    placeholder="Buscar proveedores..."
                    autocomplete="off"
                    role="combobox"
                    aria-autocomplete="list"
                    aria-expanded="false"
                    aria-controls="suggestions-suppliers"
                    data-list-search-input
                >
            </div>
            <ul class="list-search-suggestions" id="suggestions-suppliers" role="listbox" hidden></ul>
            <span class="list-search-count" data-search-count></span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover data-table align-middle">
            <thead class="data-table-head">
                <tr><th>ID</th><th>Empresa</th><th class="text-end">Acciones</th></tr>
            </thead>
            <tbody>
                @forelse ($proveedores as $proveedor)
                    <tr
                        data-search-row
                        data-search-value="{{ $proveedor->id_proveedor }} {{ $proveedor->nombre_empresa }}"
                        data-suggestion-label="{{ $proveedor->nombre_empresa }} · #{{ $proveedor->id_proveedor }}"
                    >
                        <td class="data-table-secondary">#{{ $proveedor->id_proveedor }}</td>
                        <td class="data-table-primary">{{ $proveedor->nombre_empresa }}</td>
                        <td>
                            <div class="action-buttons">
                                <button
                                    type="button"
                                    class="btn-icon btn-icon-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#supplier-products-modal-{{ $proveedor->id_proveedor }}"
                                    aria-label="Ver productos y precios de {{ $proveedor->nombre_empresa }}"
                                    title="Ver productos y precios"
                                >
                                    <i class="bi bi-eye" aria-hidden="true"></i>
                                </button>
                                <button
                                    type="button"
                                    class="btn-icon btn-icon-warning"
                                    data-bs-toggle="modal"
                                    data-bs-target="#supplier-edit-{{ $proveedor->id_proveedor }}"
                                    aria-label="Editar {{ $proveedor->nombre_empresa }}"
                                    title="Editar"
                                >
                                    <i class="bi bi-pencil" aria-hidden="true"></i>
                                </button>
                                <form action="{{ route('proveedores.destroy', $proveedor) }}" method="POST" data-confirm="¿Eliminar este proveedor y sus precios?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon btn-icon-danger" aria-label="Eliminar {{ $proveedor->nombre_empresa }}" title="Eliminar">
                                        <i class="bi bi-trash3" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="table-empty-cell">
                            @include('components.empty-state', [
                                'title' => 'Todavía no tienes proveedores',
                                'description' => 'Registra tus proveedores para relacionarlos con productos y precios de compra.',
                                'actionUrl' => '#supplier-create-modal',
                                'actionLabel' => 'Registrar proveedor',
                                'actionTarget' => 'supplier-create-modal',
                                'actionTargetType' => 'modal',
                            ])
                        </td>
                    </tr>
                @endforelse
                <tr data-search-empty hidden>
                    <td colspan="3" class="text-center text-muted py-4">No encontramos proveedores que coincidan.</td>
                </tr>
            </tbody>
        </table>
    </div>
    @if ($proveedores->hasPages())
        <div class="list-pagination" data-async-pagination>
            <span>Mostrando {{ $proveedores->firstItem() }}–{{ $proveedores->lastItem() }} de {{ $proveedores->total() }} proveedores</span>
            {{ $proveedores->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

@foreach ($proveedores as $proveedor)
    @php $reopenEditModal = $errors->any() && old('_form') === 'proveedor-'.$proveedor->id_proveedor; @endphp
    <div
        class="modal fade admin-form-modal"
        id="supplier-edit-{{ $proveedor->id_proveedor }}"
        tabindex="-1"
        aria-labelledby="supplier-edit-title-{{ $proveedor->id_proveedor }}"
        aria-hidden="true"
        data-auto-open="{{ $reopenEditModal ? 'true' : 'false' }}"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <p class="dashboard-panel-kicker mb-1">PROVEEDORES</p>
                        <h2 class="modal-title h5 fw-bold" id="supplier-edit-title-{{ $proveedor->id_proveedor }}">Editar proveedor</h2>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('proveedores.update', $proveedor) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_form" value="proveedor-{{ $proveedor->id_proveedor }}">
                        <label for="empresa-{{ $proveedor->id_proveedor }}" class="form-label">Nombre de la empresa</label>
                        <input
                            id="empresa-{{ $proveedor->id_proveedor }}"
                            type="text"
                            name="nombre_empresa"
                            class="form-control {{ $reopenEditModal && $errors->has('nombre_empresa') ? 'is-invalid' : '' }}"
                            maxlength="100"
                            value="{{ old('_form') === 'proveedor-'.$proveedor->id_proveedor ? old('nombre_empresa') : $proveedor->nombre_empresa }}"
                            required
                        >
                        @if ($reopenEditModal)
                            @error('nombre_empresa')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        @endif
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

@foreach ($proveedores as $proveedor)
    @php
        $addProductFormKey = 'proveedor-producto-'.$proveedor->id_proveedor;
        $reopenAddProductForm = $errors->any()
            && old('_return_to_list') === '1'
            && old('_form') === $addProductFormKey;
        $reopenProductsModal = session('supplier_products_modal') == $proveedor->id_proveedor
            || $reopenAddProductForm
            || ($errors->any()
                && old('_return_to_list') === '1'
                && $proveedor->productosProveedores->contains(
                    fn ($precio) => old('_form') === 'precio-'.$precio->id_producto.'-'.$proveedor->id_proveedor
                ));
    @endphp
    <div
        class="modal fade admin-form-modal supplier-products-modal"
        id="supplier-products-modal-{{ $proveedor->id_proveedor }}"
        tabindex="-1"
        aria-labelledby="supplier-products-title-{{ $proveedor->id_proveedor }}"
        aria-hidden="true"
        data-auto-open="{{ $reopenProductsModal ? 'true' : 'false' }}"
    >
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header supplier-products-modal-header">
                    <div>
                        <p class="dashboard-panel-kicker mb-1">PRODUCTOS DEL PROVEEDOR</p>
                        <h2 class="modal-title h5 fw-bold" id="supplier-products-title-{{ $proveedor->id_proveedor }}">
                            {{ $proveedor->nombre_empresa }}
                        </h2>
                    </div>
                    <div class="supplier-products-modal-controls">
                        <button
                            type="button"
                            class="btn btn-primary btn-sm text-nowrap"
                            data-bs-toggle="collapse"
                            data-bs-target="#supplier-product-create-{{ $proveedor->id_proveedor }}"
                            aria-expanded="{{ $reopenAddProductForm ? 'true' : 'false' }}"
                            aria-controls="supplier-product-create-{{ $proveedor->id_proveedor }}"
                            @disabled($productosDisponiblesPorProveedor[$proveedor->getKey()]->isEmpty())
                        >
                            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Agregar producto
                        </button>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                </div>
                <div class="modal-body">
                    @if ($productosDisponiblesPorProveedor[$proveedor->getKey()]->isNotEmpty())
                        <div
                            class="collapse {{ $reopenAddProductForm ? 'show' : '' }} mb-3"
                            id="supplier-product-create-{{ $proveedor->id_proveedor }}"
                        >
                            <form action="{{ route('proveedores.productos.store', $proveedor) }}" method="POST" class="card card-body">
                                @csrf
                                <input type="hidden" name="_form" value="{{ $addProductFormKey }}">
                                <input type="hidden" name="_return_to_list" value="1">
                                <div class="mb-3">
                                    <label for="supplier-product-{{ $proveedor->id_proveedor }}" class="form-label">Producto</label>
                                    <select
                                        id="supplier-product-{{ $proveedor->id_proveedor }}"
                                        name="id_producto"
                                        class="form-select {{ $reopenAddProductForm && $errors->has('id_producto') ? 'is-invalid' : '' }}"
                                        required
                                    >
                                        <option value="">Seleccionar producto</option>
                                        @foreach ($productosDisponiblesPorProveedor[$proveedor->getKey()] as $productoDisponible)
                                            <option
                                                value="{{ $productoDisponible->getKey() }}"
                                                @selected($reopenAddProductForm && old('id_producto') == $productoDisponible->getKey())
                                            >
                                                {{ $productoDisponible->nombre_producto }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($reopenAddProductForm)
                                        @error('id_producto')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    @endif
                                </div>
                                <div class="mb-3">
                                    <label for="supplier-product-price-{{ $proveedor->id_proveedor }}" class="form-label">Precio de compra (S/)</label>
                                    <input
                                        id="supplier-product-price-{{ $proveedor->id_proveedor }}"
                                        type="number"
                                        name="precio_compra"
                                        value="{{ $reopenAddProductForm ? old('precio_compra') : '' }}"
                                        class="form-control {{ $reopenAddProductForm && $errors->has('precio_compra') ? 'is-invalid' : '' }}"
                                        min="0"
                                        max="99999999.99"
                                        step="0.01"
                                        required
                                    >
                                    @if ($reopenAddProductForm)
                                        @error('precio_compra')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    @endif
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg me-1" aria-hidden="true"></i>Guardar producto
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                    @include('proveedores._product-cards', ['proveedor' => $proveedor, 'returnToList' => true])
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

<div class="modal fade admin-form-modal" id="supplier-create-modal" tabindex="-1" aria-labelledby="supplier-create-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <p class="dashboard-panel-kicker mb-1">PROVEEDORES</p>
                    <h2 class="modal-title h5 fw-bold" id="supplier-create-title">Nuevo proveedor</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('proveedores.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_form" value="proveedor-nuevo">
                    <label for="nombre_empresa" class="form-label">Nombre de la empresa</label>
                    <input
                        id="nombre_empresa"
                        type="text"
                        name="nombre_empresa"
                        value="{{ old('_form') === 'proveedor-nuevo' ? old('nombre_empresa') : '' }}"
                        class="form-control {{ old('_form') === 'proveedor-nuevo' && $errors->has('nombre_empresa') ? 'is-invalid' : '' }}"
                        maxlength="100"
                        placeholder="Ej. Distribuidora Majo S.A.C."
                        required
                    >
                    @if (old('_form') === 'proveedor-nuevo')
                        @error('nombre_empresa')
                            <div class="invalid-feedback d-block mb-3">{{ $message }}</div>
                        @enderror
                    @endif
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Registrar proveedor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.appFeedbackReady.then(function () {
                var createModal = document.getElementById('supplier-create-modal');
                var editModal = document.querySelector('[data-auto-open="true"]');
                var productsModal = document.querySelector('.supplier-products-modal[data-auto-open="true"]');

                if (createModal && @json($errors->any() && old('_form') === 'proveedor-nuevo')) {
                    bootstrap.Modal.getOrCreateInstance(createModal).show();
                } else if (editModal) {
                    bootstrap.Modal.getOrCreateInstance(editModal).show();
                } else if (productsModal) {
                    bootstrap.Modal.getOrCreateInstance(productsModal).show();
                }
            });
        });
    </script>
@endif
@endpush
