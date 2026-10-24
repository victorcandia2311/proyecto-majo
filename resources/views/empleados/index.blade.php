@extends('layouts.app')

@section('title', 'Empleados')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div><h1 class="h3 mb-1">Gestión de empleados</h1><p class="text-muted mb-0">Registro y administración de empleados.</p></div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#employee-create-modal">+ Registrar empleado</button>
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
        <thead class="data-table-head"><tr><th>ID</th><th>Nombres</th><th>Usuario</th><th>Documento</th><th class="text-end">Acciones</th></tr></thead>
        <tbody>
            @forelse ($empleados as $empleado)
                <tr data-search-row data-search-value="{{ $empleado->id_empleado }} {{ $empleado->nombres }} {{ $empleado->usuario }} {{ $empleado->documento_identidad }}" data-suggestion-label="{{ $empleado->nombres }} · {{ $empleado->usuario ?: 'Sin usuario' }}">
                    <td class="data-table-secondary">#{{ $empleado->id_empleado }}</td><td class="data-table-primary">{{ $empleado->nombres }}</td>
                    <td>{{ $empleado->usuario ?: 'Sin usuario' }}</td><td class="data-table-secondary">{{ $empleado->documento_identidad }}</td>
                    <td><div class="action-buttons">
                        <a href="{{ route('empleados.edit', $empleado) }}" class="btn-icon btn-icon-primary" aria-label="Editar {{ $empleado->nombres }}" title="Editar"><i class="bi bi-pencil" aria-hidden="true"></i></a>
                        <form method="POST" action="{{ route('empleados.destroy', $empleado) }}" data-confirm="¿Eliminar este empleado y sus asistencias?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-icon btn-icon-danger" aria-label="Eliminar {{ $empleado->nombres }}" title="Eliminar"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                        </form>
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="5" class="table-empty-cell">@include('components.empty-state', [
                    'title' => 'Aún no hay empleados',
                    'description' => 'Registra a tu equipo para administrar accesos y turnos.',
                    'actionUrl' => '#employee-create-modal',
                    'actionLabel' => 'Registrar empleado',
                    'actionTarget' => 'employee-create-modal',
                ])</td></tr>
            @endforelse
            <tr data-search-empty hidden><td colspan="5" class="text-center text-muted py-4">No encontramos empleados que coincidan.</td></tr>
        </tbody>
    </table></div>
</div>
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
@endpush
