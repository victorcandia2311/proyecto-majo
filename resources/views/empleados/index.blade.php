@extends('layouts.app')

@section('title', 'Empleados')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div><h1 class="h3 mb-1">Gestión de empleados</h1><p class="text-muted mb-0">Registro y administración de empleados.</p></div>
    <span class="badge rounded-pill text-bg-light border px-3 py-2 fs-6">
        <i class="bi bi-people me-1 text-primary" aria-hidden="true"></i>
        {{ $empleados->total() }} {{ Str::plural('empleado', $empleados->total()) }}
    </span>
</div>

<div id="employee-view-hub" class="module-hub">
    <button type="button" class="hub-hero hub-hero-blue" data-show-employee-view="employee-list" aria-label="Ver empleados registrados">
        <span class="hub-hero-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
        <span class="hub-hero-text">
            <strong>Ver empleados</strong>
            <span>{{ $empleados->total() }} {{ Str::plural('empleado', $empleados->total()) }} registrados en el sistema</span>
        </span>
        <i class="bi bi-arrow-right-circle ms-auto fs-4 opacity-75" aria-hidden="true"></i>
    </button>
    <div class="hub-cards">
        <button type="button" class="hub-card" data-bs-toggle="modal" data-bs-target="#employee-create-modal">
            <span class="hub-card-icon bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-person-plus" aria-hidden="true"></i>
            </span>
            <span class="hub-card-text">
                <strong>Registrar empleado</strong>
                <span>Agregar una persona al equipo</span>
            </span>
        </button>
    </div>
</div>

<div id="employee-list" class="d-none">
    <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
        <button type="button" class="view-back-btn" data-show-employee-view="employee-view-hub">
            <i class="bi bi-arrow-left" aria-hidden="true"></i> Volver al menú
        </button>
        <h2 class="h5 mb-0 me-auto">Empleados registrados</h2>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#employee-create-modal">
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Registrar empleado
        </button>
    </div>

<div class="card data-table-card" data-live-region="employees">
    <div class="card-header list-card-header">
        <h2 class="h6 mb-0">Empleados registrados</h2>
        <div class="list-search-wrap" data-list-search>
            <label class="visually-hidden" for="filter-employees">Buscar empleados</label>
            <div class="list-search-field"><i class="bi bi-search" aria-hidden="true"></i><input id="filter-employees" type="search" placeholder="Buscar empleados..." autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="suggestions-employees" data-list-search-input></div>
            <ul class="list-search-suggestions" id="suggestions-employees" role="listbox" hidden></ul>
            <span class="list-search-count" data-search-count></span>
        </div>
    </div>
    <div class="table-responsive"><table class="table table-hover data-table align-middle">
        <thead class="data-table-head"><tr><th>Nombres</th><th>Usuario</th><th class="text-end">Acciones</th></tr></thead>
        <tbody>
            @forelse ($empleados as $empleado)
                <tr data-search-row data-search-value="{{ $empleado->id_empleado }} {{ $empleado->nombres }} {{ $empleado->usuario }} {{ $empleado->documento_identidad }}" data-suggestion-label="{{ $empleado->nombres }} · {{ $empleado->usuario ?: 'Sin usuario' }}">
                    @php
                        $initials = collect(preg_split('/\s+/', trim($empleado->nombres)) ?: [])
                            ->filter()
                            ->take(2)
                            ->map(fn ($name) => Str::upper(Str::substr($name, 0, 1)))
                            ->implode('');
                    @endphp
                    <td class="data-table-primary">
                        <div class="d-flex align-items-center">
                            <span class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center fw-bold me-3"
                                  style="width: 2.5rem; height: 2.5rem;" aria-hidden="true">{{ $initials }}</span>
                            <span>{{ $empleado->nombres }}</span>
                        </div>
                    </td>
                    <td>{{ $empleado->usuario ?: 'Sin usuario' }}</td>
                    <td><div class="action-buttons">
                        <button type="button" class="btn-icon btn-light" data-bs-toggle="modal" data-bs-target="#employee-profile-{{ $empleado->id_empleado }}" aria-label="Inspeccionar perfil de {{ $empleado->nombres }}" title="Inspeccionar"><i class="bi bi-eye" aria-hidden="true"></i></button>
                        <a href="{{ route('empleados.edit', $empleado) }}" class="btn-icon btn-icon-primary" aria-label="Editar {{ $empleado->nombres }}" title="Editar"><i class="bi bi-pencil" aria-hidden="true"></i></a>
                        <form method="POST" action="{{ route('empleados.destroy', $empleado) }}" data-confirm="¿Eliminar este empleado y sus asistencias?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-icon btn-icon-danger" aria-label="Eliminar {{ $empleado->nombres }}" title="Eliminar"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                        </form>
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="3" class="table-empty-cell">@include('components.empty-state', [
                    'title' => 'Aún no hay empleados',
                    'description' => 'Registra a tu equipo para administrar accesos y turnos.',
                    'actionUrl' => '#employee-create-modal',
                    'actionLabel' => 'Registrar empleado',
                    'actionTarget' => 'employee-create-modal',
                ])</td></tr>
            @endforelse
            <tr data-search-empty hidden><td colspan="3" class="text-center text-muted py-4">No encontramos empleados que coincidan.</td></tr>
        </tbody>
    </table></div>
    @if ($empleados->hasPages())
        <div class="list-pagination" data-async-pagination>
            <span>Mostrando {{ $empleados->firstItem() }}–{{ $empleados->lastItem() }} de {{ $empleados->total() }} empleados</span>
            {{ $empleados->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
</div>
@foreach ($empleados as $empleado)
    @php
        $initials = collect(preg_split('/\s+/', trim($empleado->nombres)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn ($name) => Str::upper(Str::substr($name, 0, 1)))
            ->implode('');
    @endphp
    <div class="modal fade" id="employee-profile-{{ $empleado->id_empleado }}" tabindex="-1" aria-labelledby="employee-profile-title-{{ $empleado->id_empleado }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 pb-0">
                    <h2 class="modal-title fs-5 fw-bold" id="employee-profile-title-{{ $empleado->id_empleado }}">Perfil del empleado</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="d-flex align-items-center gap-3 p-3 rounded-3 bg-light mb-4">
                        <span class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                              style="width: 3.5rem; height: 3.5rem;" aria-hidden="true">{{ $initials }}</span>
                        <div class="min-w-0">
                            <h3 class="h5 fw-bold mb-1">{{ $empleado->nombres }}</h3>
                            <span class="text-muted">{{ $empleado->usuario ?: 'Sin usuario' }}</span>
                        </div>
                    </div>
                    <dl class="row mb-0">
                        <dt class="col-5 text-muted fw-semibold">ID de empleado</dt>
                        <dd class="col-7">#{{ $empleado->id_empleado }}</dd>
                        <dt class="col-5 text-muted fw-semibold">Documento</dt>
                        <dd class="col-7 mb-0">{{ $empleado->documento_identidad }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endforeach
<div class="modal fade" id="employee-create-modal" tabindex="-1" aria-labelledby="employee-create-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header">
                <h2 class="modal-title fs-5 fw-bold" id="employee-create-title">Registrar empleado</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('empleados.store') }}">
                    @csrf
                    <input type="hidden" name="_form" value="employee-create">
                    @include('empleados._form', ['empleado' => null])
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if ($errors->any() && old('_form') === 'employee-create')
    <script>document.addEventListener('DOMContentLoaded', () => bootstrap.Modal.getOrCreateInstance(document.getElementById('employee-create-modal')).show());</script>
@endif
<script>
(function () {
    var views = ['employee-view-hub', 'employee-list'];
    var sessionKey = 'empleados-view';

    function showView(targetId) {
        views.forEach(function (id) {
            document.getElementById(id).classList.toggle('d-none', id !== targetId);
        });
        try { sessionStorage.setItem(sessionKey, targetId); } catch (_) {}
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-show-employee-view]');
        if (button) { showView(button.dataset.showEmployeeView); }
    });

    try {
        if (sessionStorage.getItem(sessionKey) === 'employee-list') {
            showView('employee-list');
        }
    } catch (_) {}
}());
</script>
@endpush
