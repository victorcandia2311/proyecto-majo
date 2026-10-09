<div class="row g-3">
    <div class="col-md-5">
        <label for="asistencia-empleado" class="form-label">Empleado</label>
        <select name="id_empleado" id="asistencia-empleado" class="form-select" required>
            <option value="">Seleccione un empleado</option>
            @foreach ($empleados as $empleado)
                <option value="{{ $empleado->id_empleado }}" @selected(old('id_empleado', $asistencia?->id_empleado) == $empleado->id_empleado)>{{ $empleado->nombres }} — {{ $empleado->documento_identidad }}</option>
            @endforeach
        </select>
    </div>
    <div class="{{ $asistencia ? 'col-md-3' : 'col-md-7' }}">
        <label for="fecha" class="form-label">Fecha</label>
        <input type="date" id="fecha" name="fecha" value="{{ old('fecha', $asistencia?->fecha?->format('Y-m-d') ?? now()->toDateString()) }}" class="form-control" required>
    </div>
    @if ($asistencia)
        <div class="col-md-2">
            <label for="hora_ingreso" class="form-label">Hora de ingreso</label>
            <input type="time" id="hora_ingreso" name="hora_ingreso" value="{{ old('hora_ingreso', $asistencia->hora_ingreso) }}" step="1" class="form-control">
        </div>
        <div class="col-md-2">
            <label for="hora_salida" class="form-label">Hora de salida</label>
            <input type="time" id="hora_salida" name="hora_salida" value="{{ old('hora_salida', $asistencia->hora_salida) }}" step="1" class="form-control">
        </div>
    @endif
</div>
<div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-primary" @disabled($empleados->isEmpty())>{{ $asistencia ? 'Guardar cambios' : 'Registrar asistencia' }}</button>
    @if ($asistencia)<a href="{{ route('asistencia.index') }}" class="btn btn-secondary">Cancelar</a>@endif
</div>
