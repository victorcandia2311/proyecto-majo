@extends('layouts.app')

@section('title', 'Nuevo producto')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div><h1 class="h3 mb-1">Productos</h1><p class="text-muted mb-0">Registra un producto para agregarlo al catálogo.</p></div>
    <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary rounded-3 px-3 py-2">Volver al catálogo</a>
</div>
<div class="offcanvas offcanvas-end admin-form-panel" tabindex="-1" id="product-create-panel" aria-labelledby="product-create-title">
    <div class="offcanvas-header border-bottom">
        <div><p class="dashboard-panel-kicker mb-1">CATÁLOGO</p><h2 class="offcanvas-title h5 fw-bold" id="product-create-title">Nuevo producto</h2></div>
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
<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.appFeedbackReady.then(() => {
            bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('product-create-panel')).show();
        });
    });
</script>
@endpush
