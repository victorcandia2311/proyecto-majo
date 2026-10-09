<div class="row g-3">
    <div class="col-md-4"><label for="nombres" class="form-label">Nombres completos</label>
    <input type="text" id="nombres" name="nombres" class="form-control" value="{{ old('nombres', $empleado?->nombres) }}" maxlength="100" required>
</div>
    <div class="col-md-4"><label for="usuario" class="form-label">Usuario de vendedor</label>
    <input type="text" id="usuario" name="usuario" class="form-control" value="{{ old('usuario', $empleado?->usuario) }}" maxlength="50" pattern="[A-Za-z0-9_\-]+" autocomplete="off" placeholder="usuario_01" required>
    <div class="form-text">Este es el usuario para iniciar sesión como vendedor.</div>
</div>
    <div class="col-md-4"><label for="documento_identidad" class="form-label">Documento de identidad</label>
    <input type="text" id="documento_identidad" name="documento_identidad" class="form-control" value="{{ old('documento_identidad', $empleado?->documento_identidad) }}" inputmode="numeric" pattern="[0-9]{8}" minlength="8" maxlength="8" data-digits-only required>
</div>
</div>
<div class="d-flex gap-2 mt-4">
    <button class="btn btn-primary" type="submit">{{ $empleado ? 'Guardar cambios' : 'Registrar empleado' }}</button>
    @if ($empleado)<a href="{{ route('empleados.index') }}" class="btn btn-secondary">Cancelar</a>@endif
</div>
