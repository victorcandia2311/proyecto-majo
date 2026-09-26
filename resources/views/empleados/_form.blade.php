<div class="row g-3">
    <div class="col-md-4"><label for="nombres" class="form-label">Nombres completos</label>
    <input type="text" id="nombres" name="nombres" class="form-control" value="{{ old('nombres', $empleado?->nombres) }}" maxlength="100" required>
</div>
    <div class="col-md-3"><label for="usuario" class="form-label">Usuario de vendedor</label>
    <input type="text" id="usuario" name="usuario" class="form-control" value="{{ old('usuario', $empleado?->usuario) }}" maxlength="50" pattern="[A-Za-z0-9_-]+" autocomplete="off">
</div>
    <div class="col-md-3"><label for="documento_identidad" class="form-label">Documento de identidad</label>
    <input type="text" id="documento_identidad" name="documento_identidad" class="form-control" value="{{ old('documento_identidad', $empleado?->documento_identidad) }}" maxlength="20" required>
</div>
    <div class="col-md-2"><label for="es_eventual" class="form-label">Tipo</label>
        <select id="es_eventual" name="es_eventual" class="form-select" required>
            <option value="false" @selected(! in_array(old('es_eventual', $empleado?->es_eventual), [true, 1, '1', 'true'], true))>Permanente</option>
            <option value="true" @selected(in_array(old('es_eventual', $empleado?->es_eventual), [true, 1, '1', 'true'], true))>Eventual</option>
        </select>
    </div>
</div>
<div class="d-flex gap-2 mt-4">
    <button class="btn btn-primary" type="submit">{{ $empleado ? 'Guardar cambios' : 'Registrar empleado' }}</button>
    @if ($empleado)<a href="{{ route('empleados.index') }}" class="btn btn-secondary">Cancelar</a>@endif
</div>
