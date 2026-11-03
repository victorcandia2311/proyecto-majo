@extends('layouts.app')

@section('title', 'Productos')

{{-- Styles come from theme.css (hub-hero, hub-card, view-back-btn) --}}

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

{{-- ══════════════════════════════════════════════════════════════════
     VISTA A: MODULE HUB (visible por defecto)
     ══════════════════════════════════════════════════════════════════ --}}
<div id="view-hub" class="module-hub">

    <button type="button" class="hub-hero hub-hero-blue" data-show-view="view-table" aria-label="Ver lista de productos">
        <span class="hub-hero-icon"><i class="bi bi-table" aria-hidden="true"></i></span>
        <span class="hub-hero-text">
            <strong>Ver lista de productos</strong>
            <span>Consulta, filtra y gestiona el catálogo completo</span>
        </span>
        <i class="bi bi-arrow-right-circle ms-auto fs-4 opacity-75" aria-hidden="true"></i>
    </button>

    <div class="hub-cards">
        <button
            type="button"
            class="hub-card"
            data-bs-toggle="offcanvas"
            data-bs-target="#product-create-panel"
            aria-controls="product-create-panel"
        >
            <span class="hub-card-icon bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-plus-circle" aria-hidden="true"></i>
            </span>
            <span class="hub-card-text">
                <strong>Nuevo producto</strong>
                <span>Registrar en catálogo</span>
            </span>
        </button>


    </div>

    @php $stockBajo = $productos->filter(fn($p) => $p->stock_actual <= $p->stock_minimo_alerta)->count(); @endphp
    @if ($stockBajo > 0)
        <div class="alert alert-danger d-flex align-items-center gap-2 rounded-3 mb-0" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5" aria-hidden="true"></i>
            <span>
                <strong>{{ $stockBajo }} {{ Str::plural('producto', $stockBajo) }}</strong> con stock bajo.
                <button type="button" class="alert-link btn btn-link p-0 align-baseline" data-show-view="view-table">Ver lista →</button>
            </span>
        </div>
    @endif

</div>{{-- /view-hub --}}

{{-- ══════════════════════════════════════════════════════════════════
     VISTA B: TABLA DE PRODUCTOS (oculta por defecto)
     ══════════════════════════════════════════════════════════════════ --}}
<div id="view-table" class="d-none">

    <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
        <button type="button" class="view-back-btn" data-show-view="view-hub">
            <i class="bi bi-arrow-left" aria-hidden="true"></i> Volver al menú
        </button>
        <h2 class="h5 mb-0 me-auto">Lista de productos</h2>
        <button
            type="button"
            class="btn btn-primary btn-sm"
            data-bs-toggle="offcanvas"
            data-bs-target="#product-create-panel"
            aria-controls="product-create-panel"
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
                            data-search-value="{{ $producto->id_producto }} {{ $producto->nombre_producto }} {{ $producto->stock_actual }} {{ $producto->stock_minimo_alerta }} {{ $producto->precio_venta }} {{ $producto->stock_actual <= $producto->stock_minimo_alerta ? 'stock bajo' : 'disponible' }}"
                            data-suggestion-label="{{ $producto->nombre_producto }} · #{{ $producto->id_producto }}"
                        >
                            <td class="data-table-primary">{{ $producto->nombre_producto }}</td>
                            <td class="data-table-number">{{ $producto->stock_actual }}</td>
                            <td class="data-table-number">S/ {{ number_format((float) $producto->precio_venta, 2) }}</td>
                            <td>
                                @if ($producto->stock_actual <= $producto->stock_minimo_alerta)
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
                                    <a href="{{ route('productos.edit', $producto) }}" class="btn-icon btn-icon-primary" aria-label="Editar {{ $producto->nombre_producto }}" title="Editar">
                                        <i class="bi bi-pencil" aria-hidden="true"></i>
                                    </a>
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
                                    'actionUrl'        => '#product-create-panel',
                                    'actionLabel'      => 'Registrar producto',
                                    'actionTarget'     => 'product-create-panel',
                                    'actionTargetType' => 'offcanvas',
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
                                    @if ($producto->stock_actual <= $producto->stock_minimo_alerta)
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

{{-- ── Offcanvas: Nuevo producto ─────────────────────────────────────── --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="product-create-panel" aria-labelledby="product-create-title">
    <div class="offcanvas-header border-bottom">
        <div>
            <p class="dashboard-panel-kicker mb-1">CATÁLOGO</p>
            <h2 class="offcanvas-title h5 fw-bold" id="product-create-title">Nuevo producto</h2>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body">
        <form action="{{ route('productos.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_form" value="product-create">
            @include('productos._form', ['producto' => null])
        </form>
    </div>
</div>

@endsection

@push('scripts')
@if ($errors->any() && old('_form') === 'product-create')
    <script>
        document.addEventListener('DOMContentLoaded', () =>
            window.appFeedbackReady.then(() =>
                bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('product-create-panel')).show()
            )
        );
    </script>
@endif

<script>
(function () {
    var VIEWS = ['view-hub', 'view-table'];
    var SESSION_KEY = 'productos-view';

    function showView(targetId) {
        VIEWS.forEach(function (id) {
            document.getElementById(id).classList.toggle('d-none', id !== targetId);
        });
        try { sessionStorage.setItem(SESSION_KEY, targetId); } catch (_) {}
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-show-view]');
        if (btn) showView(btn.dataset.showView);
    });

    // Restore last view after a form submit / page reload
    var stored = (function () { try { return sessionStorage.getItem(SESSION_KEY); } catch (_) { return null; } }());
    if (stored && stored !== 'view-hub') showView(stored);
}());
</script>
@endpush
