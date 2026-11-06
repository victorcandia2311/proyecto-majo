@extends('layouts.app')

@section('title', 'Nuevo producto')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div><h1 class="h3 mb-1">Productos</h1><p class="text-muted mb-0">Registra un producto para agregarlo al catálogo.</p></div>
    <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary rounded-3 px-3 py-2">Volver al catálogo</a>
</div>
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
                    @include('productos._form', ['producto' => null])
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.appFeedbackReady.then(() => {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('product-create-modal')).show();
        });
    });
</script>
@endpush
