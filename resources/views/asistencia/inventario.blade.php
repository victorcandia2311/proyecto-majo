@extends('layouts.app')

@section('title', 'Inventario de asistencia')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h1 class="h3">Inventario diario de asistencia</h1>
        <p class="text-muted mb-0">Consulta las asistencias de empleados y empleados temporales por fecha.</p>
    </div>
    <a href="{{ route('asistencia.index') }}" class="btn btn-outline-secondary">Volver a Asistencia</a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('asistencia.inventario') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-sm-6 col-md-4">
                <label for="fecha-inventario-asistencia" class="form-label">Fecha</label>
                <input type="date" id="fecha-inventario-asistencia" name="fecha" value="{{ $fecha }}" class="form-control" required>
            </div>
            <div class="col-sm-6 col-md-3">
                <button type="submit" class="btn btn-primary">Consultar</button>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h2 class="h6 mb-0">Empleados</h2></div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr><th>Empleado</th><th>Asistencia</th><th>Hora de ingreso</th><th>Hora de salida</th></tr>
            </thead>
            <tbody>
                @forelse ($empleados as $empleado)
                    @php($asistencia = $asistencias->get($empleado->id_empleado))
                    <tr>
                        <td>{{ $empleado->nombres }}</td>
                        <td>
                            @if ($asistencia)
                                <span class="badge bg-success">Asistió</span>
                            @else
                                <span class="badge bg-danger">No asistió</span>
                            @endif
                        </td>
                        <td class="text-nowrap">{{ $asistencia?->hora_ingreso ?? '—' }}</td>
                        <td class="text-nowrap">{{ $asistencia?->hora_salida ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No hay empleados registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header"><h2 class="h6 mb-0">Empleados temporales</h2></div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr><th>Empleado temporal</th><th>Hora de ingreso</th><th>Hora de salida</th></tr>
            </thead>
            <tbody>
                @forelse ($empleadosTemporales as $empleadoTemporal)
                    <tr>
                        <td>{{ $empleadoTemporal->nombre_temp ?? $empleadoTemporal->empleado?->nombres ?? 'Empleado temporal' }}</td>
                        <td class="text-nowrap">{{ $empleadoTemporal->hora_ingreso ?? '—' }}</td>
                        <td class="text-nowrap">{{ $empleadoTemporal->hora_salida ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">No hay asistencias temporales registradas para esta fecha.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
