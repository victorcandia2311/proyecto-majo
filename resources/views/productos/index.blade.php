@extends('layouts.app')

@section('title', 'Productos')

{{-- Styles come from theme.css (hub-card, view-back-btn) --}}

@section('content')

{{-- ── Page header ────────────────────────────────────────────────── --}}
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Productos</h1>
        <p class="text-muted mb-0">Gestión de productos e inventario.</p>
    </div>
    <span class="badge rounded-pill text-bg-light border px-3 py-2 fs-6">
        <i class="bi bi-box2 me-1 text-primary" aria-hidden="true"></i>
        {{ $productos->total() }} {{ Str::plural('producto', $productos->total()) }}
    </span>
</div>

@php $stockBajo = $productos->filter(fn($p) => $p->stock_actual <= $p->stock_minimo_alerta)->count(); @endphp
@if ($stockBajo > 0)
    <div class="alert alert-danger d-flex align-items-center gap-2 rounded-3 mb-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-5" aria-hidden="true"></i>
        <span>
            <strong>{{ $stockBajo }} {{ Str::plural('producto', $stockBajo) }}</strong> con stock bajo.
            Revisa la lista de productos.
        </span>
    </div>
@endif

<div id="view-table">

    <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
        <h2 class="h5 mb-0 me-auto">Lista de productos</h2>
        <button
            type="button"
            class="btn btn-primary btn-sm"
            data-bs-toggle="modal"
            data-bs-target="#product-create-modal"
        >
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Nuevo producto
        </button>
    </div>

    <div class="card data-table-card" data-live-region="products">
        <div class="card-header list-card-header">
            <h2 class="h6 mb-0">Catálogo</h2>
            <div class="list-search-wrap" data-list-search>
                <label class="visually-hidden" for="filter-products">Buscar productos</label>
                <div class="list-search-field">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input
                        id="filter-products"
                        type="search"
                        placeholder="Buscar productos..."
                        autocomplete="off"
                        role="combobox"
                        aria-autocomplete="list"
                        aria-expanded="false"
                        aria-controls="suggestions-products"
                        data-list-search-input
                    >
                </div>
                <ul class="list-search-suggestions" id="suggestions-products" role="listbox" hidden></ul>
                <span class="list-search-count" data-search-count></span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover data-table align-middle">
                <thead class="data-table-head">
                    <tr>
                        <th>Producto</th><th>Stock</th><th>Precio</th><th>Estado</th><th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($productos as $producto)
                        <tr
                            data-search-row
                            data-search-value="{{ $producto->id_producto }} {{ $producto->nombre_producto }} {{ $producto->stock_actual }} {{ $producto->stock_minimo_alerta }} {{ $producto->precio_venta }} {{ $producto->stock_actual === 0 ? 'no disponible' : ($producto->stock_actual <= $producto->stock_minimo_alerta ? 'stock bajo' : 'disponible') }}"
                            data-suggestion-label="{{ $producto->nombre_producto }} · #{{ $producto->id_producto }}"
                        >
                            <td class="data-table-primary">{{ $producto->nombre_producto }}</td>
                            <td class="data-table-number">{{ $producto->stock_actual }}</td>
                            <td class="data-table-number">S/ {{ number_format((float) $producto->precio_venta, 2) }}</td>
                            <td>
                                @if ($producto->stock_actual === 0)
                                    <span class="data-badge data-badge-danger"><i class="bi bi-x-circle" aria-hidden="true"></i>No disponible</span>
                                @elseif ($producto->stock_actual <= $producto->stock_minimo_alerta)
                                    <span class="data-badge data-badge-danger"><i class="bi bi-exclamation-circle" aria-hidden="true"></i>Stock bajo</span>
                                @else
                                    <span class="data-badge data-badge-success"><i class="bi bi-check-circle" aria-hidden="true"></i>Disponible</span>
                                @endif
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button type="button" class="btn-icon btn-light" data-bs-toggle="modal" data-bs-target="#product-inspect-{{ $producto->id_producto }}" aria-label="Inspeccionar {{ $producto->nombre_producto }}" title="Inspeccionar">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </button>
                                    <button type="button" class="btn-icon btn-icon-primary" data-bs-toggle="modal" data-bs-target="#product-edit-modal-{{ $producto->id_producto }}" aria-label="Editar {{ $producto->nombre_producto }}" title="Editar">
                                        <i class="bi bi-pencil" aria-hidden="true"></i>
                                    </button>
                                    <form method="POST" action="{{ route('productos.destroy', $producto) }}" data-confirm="¿Deseas eliminar este producto?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon btn-icon-danger" aria-label="Eliminar {{ $producto->nombre_producto }}" title="Eliminar">
                                            <i class="bi bi-trash3" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="table-empty-cell">
                                @include('components.empty-state', [
                                    'title'            => 'Tu catálogo está vacío',
                                    'description'      => 'Registra el primer producto para empezar a controlar existencias y ventas.',
                                    'actionUrl'        => '#product-create-modal',
                                    'actionLabel'      => 'Registrar producto',
                                    'actionTarget'     => 'product-create-modal',
                                    'actionTargetType' => 'modal',
                                ])
                            </td>
                        </tr>
                    @endforelse
                    <tr data-search-empty hidden>
                        <td colspan="5" class="text-center text-muted py-4">No encontramos productos que coincidan.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @if ($productos->hasPages())
            <div class="list-pagination" data-async-pagination>
                <span>Mostrando {{ $productos->firstItem() }}–{{ $productos->lastItem() }} de {{ $productos->total() }} productos</span>
                {{ $productos->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

</div>{{-- /view-table --}}

@foreach ($productos as $producto)
    <div class="modal fade" id="product-inspect-{{ $producto->id_producto }}" tabindex="-1" aria-labelledby="product-inspect-title-{{ $producto->id_producto }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow overflow-hidden">
                <div class="modal-header border-0 pb-0">
                    <h2 class="modal-title fs-5 fw-bold" id="product-inspect-title-{{ $producto->id_producto }}">Detalle del producto</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4 align-items-center">
                        <div class="col-md-5">
                            @if ($producto->imagen_path)
                                <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($producto->imagen_path) }}" alt="Imagen de {{ $producto->nombre_producto }}" class="img-fluid rounded-4 w-100" style="max-height: 300px; object-fit: cover;">
                            @else
                                <div class="rounded-4 bg-light d-flex align-items-center justify-content-center text-secondary" style="height: 240px;">
                                    <i class="bi bi-image fs-1" aria-hidden="true"></i>
                                    <span class="visually-hidden">Sin imagen disponible</span>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-7">
                            <p class="text-uppercase small fw-bold text-muted mb-2">Producto #{{ $producto->id_producto }}</p>
                            <h3 class="h4 fw-bold mb-2">{{ $producto->nombre_producto }}</h3>
                            <p class="text-secondary mb-4">{{ $producto->descripcion ?: 'Sin descripción registrada.' }}</p>
                            <p class="h4 fw-bold text-primary mb-4">S/ {{ number_format((float) $producto->precio_venta, 2) }}</p>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="p-3 rounded-3 bg-light h-100">
                                        <span class="d-block small text-muted mb-1">Stock actual</span>
                                        <strong class="fs-5">{{ $producto->stock_actual }}</strong>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 rounded-3 bg-light h-100">
                                        <span class="d-block small text-muted mb-1">Stock mínimo</span>
                                        <strong class="fs-5">{{ $producto->stock_minimo_alerta }}</strong>
                                    </div>
                                </div>
                                <div class="col-12">
                                    @if ($producto->stock_actual === 0)
                                        <span class="data-badge data-badge-danger"><i class="bi bi-x-circle" aria-hidden="true"></i>No disponible</span>
                                    @elseif ($producto->stock_actual <= $producto->stock_minimo_alerta)
                                        <span class="data-badge data-badge-danger"><i class="bi bi-exclamation-circle" aria-hidden="true"></i>Stock bajo</span>
                                    @else
                                        <span class="data-badge data-badge-success"><i class="bi bi-check-circle" aria-hidden="true"></i>Disponible</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach

@foreach ($productos as $producto)
    <div class="modal fade admin-form-modal" id="product-edit-modal-{{ $producto->id_producto }}" tabindex="-1" aria-labelledby="product-edit-title-{{ $producto->id_producto }}" aria-hidden="true" @if ($errors->any() && old('_form') === 'product-edit-'.$producto->id_producto) data-auto-open @endif>
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <p class="dashboard-panel-kicker mb-1">CATÁLOGO</p>
                        <h2 class="modal-title h5 fw-bold" id="product-edit-title-{{ $producto->id_producto }}">Editar producto</h2>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('productos.update', $producto) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_form" value="product-edit-{{ $producto->id_producto }}">
                        @include('productos._form', [
                            'producto' => $producto,
                            'formInModal' => true,
                            'formKey' => 'product-edit-'.$producto->id_producto,
                            'formIdPrefix' => 'product-edit-'.$producto->id_producto.'-',
                        ])
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

<div class="modal fade admin-form-modal" id="product-create-modal" tabindex="-1" aria-labelledby="product-create-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <p class="dashboard-panel-kicker mb-1">CATÁLOGO</p>
                    <h2 class="modal-title h5 fw-bold" id="product-create-title">Nuevo producto</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('productos.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_form" value="product-create">
                    @include('productos._form', [
                        'producto' => null,
                        'formInModal' => true,
                        'formKey' => 'product-create',
                        'formIdPrefix' => 'product-create-',
                    ])
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
@if ($errors->any() && (old('_form') === 'product-create' || str_starts_with((string) old('_form'), 'product-edit-')))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.querySelector('[data-auto-open]') || document.getElementById('product-create-modal');
            window.appFeedbackReady.then(() => bootstrap.Modal.getOrCreateInstance(modal).show());
        });
    </script>
@endif

@endpush
