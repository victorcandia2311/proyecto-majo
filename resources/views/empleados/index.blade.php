@extends('layouts.app')

@section('title', 'Empleados')

@section('content')
<h1 class="h3">Gestión de empleados</h1>
<p class="text-muted mb-4">Registro y administración de empleados.</p>
<div class="card mb-4">
    <div class="card-header"><h2 class="h6 mb-0">Registrar empleado</h2></div>
    <div class="card-body"><form method="POST" action="{{ route('empleados.store') }}">
        @csrf
        @include('empleados._form', ['empleado' => null])
    </form></div>
</div>
<div class="card" data-live-region="employees">
    <div class="card-header list-card-header">
        <h2 class="h6 mb-0">Empleados registrados</h2>
        <div class="list-search-wrap" data-list-search>
            <label class="visually-hidden" for="filter-employees">Buscar empleados</label>
            <div class="list-search-field"><i class="bi bi-search" aria-hidden="true"></i><input id="filter-employees" type="search" placeholder="Buscar empleados..." autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="suggestions-employees" data-list-search-input></div>
            <ul class="list-search-suggestions" id="suggestions-employees" role="listbox" hidden></ul>
            <span class="list-search-count" data-search-count></span>
        </div>
    </div>
    <div class="table-responsive"><table class="table table-bordered table-hover align-middle">
        <thead class="table-dark"><tr><th>ID</th><th>Nombres</th><th>Usuario</th><th>Documento</th><th>Tipo</th><th>Acciones</th></tr></thead>
        <tbody>
            @forelse ($empleados as $empleado)
                <tr data-search-row data-search-value="{{ $empleado->id_empleado }} {{ $empleado->nombres }} {{ $empleado->usuario }} {{ $empleado->documento_identidad }} {{ $empleado->es_eventual ? 'eventual' : 'permanente' }}" data-suggestion-label="{{ $empleado->nombres }} · {{ $empleado->usuario ?: 'Sin usuario' }}">
                    <td>{{ $empleado->id_empleado }}</td><td>{{ $empleado->nombres }}</td><td>{{ $empleado->usuario ?: 'Sin usuario' }}</td><td>{{ $empleado->documento_identidad }}</td>
                    <td><span class="badge {{ $empleado->es_eventual ? 'bg-warning text-dark' : 'bg-success' }}">{{ $empleado->es_eventual ? 'Eventual' : 'Permanente' }}</span></td>
                    <td><div class="action-buttons">
                        <a href="{{ route('empleados.edit', $empleado) }}" class="btn btn-warning btn-sm">Editar</a>
                        <form method="POST" action="{{ route('empleados.destroy', $empleado) }}" data-confirm="¿Eliminar este empleado y sus asistencias?">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
</form>
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No hay empleados registrados.</td></tr>
            @endforelse
            <tr data-search-empty hidden><td colspan="6" class="text-center text-muted py-4">No encontramos empleados que coincidan.</td></tr>
        </tbody>
    </table></div>
</div>
@endsection
