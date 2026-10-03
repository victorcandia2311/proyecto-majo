@extends('layouts.app')

@section('title', 'Asistencia')

@section('content')
<h1 class="h3">Control de asistencia</h1>
<p class="text-muted mb-4">Registra asistencias manualmente o marca el ingreso y la salida del día.</p>
<div class="card mb-4">
    <div class="card-header"><h2 class="h6 mb-0">Registrar asistencia</h2></div>
    <div class="card-body">
        <form action="{{ route('asistencia.store') }}" method="POST">
            @csrf
            @include('asistencia._form', ['asistencia' => null, 'empleados' => $empleados])
        </form>
    </div>
</div>
<div class="card mb-4">
    <div class="card-header"><h2 class="h6 mb-0">Marcar ingreso o salida</h2></div>
    <div class="card-body">
        <form action="{{ route('asistencia.ingreso') }}" method="POST" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-6">
                <label for="marcacion-empleado" class="form-label">Empleado</label>
                <select name="id_empleado" id="marcacion-empleado" class="form-select" required>
                    <option value="">Seleccione un empleado</option>
                    @foreach ($empleados as $empleado)
                        <option value="{{ $empleado->id_empleado }}" @selected(old('id_empleado') == $empleado->id_empleado)>{{ $empleado->nombres }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-success" @disabled($empleados->isEmpty())>Registrar ingreso</button>
                <button type="submit" formaction="{{ route('asistencia.salida') }}" class="btn btn-primary" @disabled($empleados->isEmpty())>Registrar salida</button>
            </div>
        </form>
        @if ($empleados->isEmpty())
            <p class="text-muted mt-3 mb-0">
                @if (auth()->user()->isAdmin())
                    Primero <a href="{{ route('empleados.index') }}">registra un empleado</a>.
                @else
                    Solicita al administrador que registre un empleado.
                @endif
            </p>
        @endif
    </div>
</div>
<div class="card" data-live-region="attendance">
    <div class="card-header list-card-header">
        <h2 class="h6 mb-0">Asistencias registradas</h2>
        <div class="list-search-wrap" data-list-search>
            <label class="visually-hidden" for="filter-attendance">Buscar asistencias</label>
            <div class="list-search-field"><i class="bi bi-search" aria-hidden="true"></i><input id="filter-attendance" type="search" placeholder="Buscar por empleado o fecha..." autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="suggestions-attendance" data-list-search-input></div>
            <ul class="list-search-suggestions" id="suggestions-attendance" role="listbox" hidden></ul>
            <span class="list-search-count" data-search-count></span>
        </div>
    </div>
    <div class="table-responsive"><table class="table table-bordered table-hover align-middle">
        <thead class="table-dark"><tr><th>ID</th><th>Empleado</th><th>Documento</th><th>Fecha</th><th>Ingreso</th><th>Salida</th><th>Acciones</th></tr></thead>
        <tbody>
            @forelse ($asistencias as $asistencia)
                <tr data-search-row data-search-value="{{ $asistencia->id_asistencia }} {{ $asistencia->empleado?->nombres ?? 'Empleado eliminado' }} {{ $asistencia->empleado?->documento_identidad }} {{ $asistencia->fecha->format('d/m/Y') }} {{ $asistencia->hora_ingreso }} {{ $asistencia->hora_salida }}" data-suggestion-label="{{ $asistencia->empleado?->nombres ?? 'Empleado eliminado' }} · {{ $asistencia->fecha->format('d/m/Y') }}">
                    <td>{{ $asistencia->id_asistencia }}</td><td>{{ $asistencia->empleado?->nombres ?? 'Empleado eliminado' }}</td>
                    <td>{{ $asistencia->empleado?->documento_identidad ?? '—' }}</td><td class="text-nowrap">{{ $asistencia->fecha->format('d/m/Y') }}</td>
                    <td>{{ $asistencia->hora_ingreso ?? '—' }}</td><td>{{ $asistencia->hora_salida ?? '—' }}</td>
                    <td><div class="action-buttons">
                        <a href="{{ route('asistencia.edit', $asistencia) }}" class="btn btn-warning btn-sm">Editar</a>
                        <form action="{{ route('asistencia.destroy', $asistencia) }}" method="POST" data-confirm="¿Eliminar esta asistencia?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                        </form>
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No hay asistencias registradas.</td></tr>
            @endforelse
            <tr data-search-empty hidden><td colspan="7" class="text-center text-muted py-4">No encontramos asistencias que coincidan.</td></tr>
        </tbody>
    </table></div>
</div>
@endsection
