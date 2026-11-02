@extends('layouts.app')

@section('title', 'Asistencia')

@push('styles')
<style>
    /* ═══ Modal form fields ═══════════════════════════════════════════════ */
    .asistencia-modal .modal-content {
        border: 0;
        border-radius: 1rem;
        box-shadow: 0 20px 60px rgba(15, 23, 42, .18);
    }
    .asistencia-modal .modal-header {
        border-bottom: 1px solid #f0f2f5;
        padding: 1.25rem 1.5rem .9rem;
    }
    .asistencia-modal .modal-body  { padding: 1.25rem 1.5rem; }
    .asistencia-modal .modal-footer {
        border-top: 1px solid #f0f2f5;
        padding: .9rem 1.5rem 1.25rem;
        gap: .5rem;
    }
    .asistencia-modal .modal-title-icon {
        display: inline-grid;
        width: 34px;
        height: 34px;
        place-items: center;
        border-radius: .6rem;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .asistencia-modal .form-label {
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #6b7280;
        margin-bottom: .35rem;
    }
    .asistencia-modal .form-control,
    .asistencia-modal .form-select {
        min-height: 44px;
        border-radius: .65rem;
        border-color: #e5e7eb;
        font-size: .9rem;
    }
    .asistencia-modal .form-control:focus,
    .asistencia-modal .form-select:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99,102,241,.15);
    }
</style>
@endpush

@section('content')

{{-- ═══ Page header ══════════════════════════════════════════════════════════ --}}
<div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Control de asistencia</h1>
        <p class="text-muted mb-0">Marca el ingreso y la salida de los empleados para registrar sus horas trabajadas.</p>
    </div>
    <a href="{{ route('asistencia.inventario') }}" class="btn btn-outline-primary btn-sm align-self-start">
        <i class="bi bi-clipboard-data me-1" aria-hidden="true"></i>Inventario de asistencia
    </a>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════
     PANTALLA 1: MENÚ CENTRAL (visible por defecto)
     ═══════════════════════════════════════════════════════════════════════════ --}}
<div id="menu-hub">

    <div class="row row-cols-1 row-cols-md-3 g-4 mb-2">

        {{-- Botón 1: Ver asistencias de hoy --}}
        <div class="col">
            <button type="button" class="hub-card h-100 w-100" data-show-view="tabla-staff">
                <span class="hub-card-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-calendar-check" aria-hidden="true"></i>
                </span>
                <span class="hub-card-text">
                    <strong>Asistencias de Hoy</strong>
                    <span>Registros del día actual</span>
                </span>
                <i class="bi bi-arrow-right ms-auto text-secondary" aria-hidden="true"></i>
            </button>
        </div>

        {{-- Botón 2: Marcar ingreso/salida personal fijo --}}
        <div class="col">
            <button type="button" class="hub-card h-100 w-100"
                    data-bs-toggle="modal" data-bs-target="#modal-staff"
                    @class(['opacity-50' => $empleados->isEmpty()])>
                <span class="hub-card-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-person-check" aria-hidden="true"></i>
                </span>
                <span class="hub-card-text">
                    <strong>Marcar Ingreso / Salida</strong>
                    <span>Personal Fijo</span>
                </span>
                <i class="bi bi-arrow-right ms-auto text-secondary" aria-hidden="true"></i>
            </button>
        </div>

        {{-- Botón 3: Gestión de temporales (marcar + registrar) --}}
        <div class="col">
            <button type="button" class="hub-card h-100 w-100" data-show-view="tabla-temporales">
                <span class="hub-card-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-people" aria-hidden="true"></i>
                </span>
                <span class="hub-card-text">
                    <strong>Gestión de Temporales</strong>
                    <span>Registrar y marcar personal temporal</span>
                </span>
                <i class="bi bi-arrow-right ms-auto text-secondary" aria-hidden="true"></i>
            </button>
        </div>

    </div>

    @if ($empleados->isEmpty())
        <div class="alert alert-warning d-flex align-items-center gap-2 rounded-3 mb-0" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5" aria-hidden="true"></i>
            <span>
                No hay empleados de personal fijo registrados.
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('empleados.index') }}" class="alert-link">Registra un empleado</a>
                @else
                    Solicita al administrador que registre un empleado.
                @endif
            </span>
        </div>
    @endif

</div>{{-- /menu-hub --}}

{{-- ═══════════════════════════════════════════════════════════════════════════
     PANTALLA 2: TABLA — Asistencias de Staff (oculta por defecto)
     ═══════════════════════════════════════════════════════════════════════════ --}}
<div id="tabla-staff" class="d-none">

    <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
        <button type="button" class="view-back-btn" data-show-view="menu-hub">
            <i class="bi bi-arrow-left" aria-hidden="true"></i> Volver al menú
        </button>
        <h2 class="h5 mb-0 me-auto">Asistencias registradas hoy</h2>
        <button
            type="button"
            class="btn btn-primary btn-sm"
            data-bs-toggle="modal"
            data-bs-target="#modal-staff"
            @disabled($empleados->isEmpty())
        >
            <i class="bi bi-person-check me-1" aria-hidden="true"></i>Marcar Asistencia
        </button>
    </div>

    <div class="card data-table-card mb-4" data-live-region="attendance">
        <div class="card-header list-card-header">
            <h2 class="h6 mb-0">Asistencias registradas hoy</h2>
            <div class="list-search-wrap" data-list-search>
                <label class="visually-hidden" for="filter-attendance">Buscar asistencias</label>
                <div class="list-search-field">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input id="filter-attendance" type="search" placeholder="Buscar por empleado o fecha..." autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="suggestions-attendance" data-list-search-input>
                </div>
                <ul class="list-search-suggestions" id="suggestions-attendance" role="listbox" hidden></ul>
                <span class="list-search-count" data-search-count></span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover data-table align-middle">
                <thead class="data-table-head">
                    <tr>
                        <th>ID</th>
                        <th>Empleado</th>
                        <th>Documento</th>
                        <th>Fecha</th>
                        <th>Ingreso</th>
                        <th>Salida</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($asistencias as $asistencia)
                        <tr
                            data-search-row
                            data-search-value="{{ $asistencia->id_asistencia }} {{ $asistencia->empleado?->nombres ?? 'Empleado eliminado' }} {{ $asistencia->empleado?->documento_identidad }} {{ $asistencia->fecha->format('d/m/Y') }} {{ $asistencia->hora_ingreso }} {{ $asistencia->hora_salida }}"
                            data-suggestion-label="{{ $asistencia->empleado?->nombres ?? 'Empleado eliminado' }} — {{ $asistencia->fecha->format('d/m/Y') }}"
                        >
                            <td class="data-table-secondary">#{{ $asistencia->id_asistencia }}</td>
                            <td class="data-table-primary">{{ $asistencia->empleado?->nombres ?? 'Empleado eliminado' }}</td>
                            <td class="data-table-secondary">{{ $asistencia->empleado?->documento_identidad ?? '—' }}</td>
                            <td class="text-nowrap data-table-secondary">{{ $asistencia->fecha->format('d/m/Y') }}</td>
                            <td class="data-table-number">{{ $asistencia->hora_ingreso ?? '—' }}</td>
                            <td class="data-table-number">{{ $asistencia->hora_salida ?? '—' }}</td>
                            <td>
                                <div class="action-buttons">
                                    <form action="{{ route('asistencia.destroy', $asistencia) }}" method="POST" data-confirm="¿Eliminar esta asistencia?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon btn-icon-danger" aria-label="Eliminar asistencia de {{ $asistencia->empleado?->nombres ?? 'empleado' }}" title="Eliminar"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="table-empty-cell">
                                @include('components.empty-state', [
                                    'title'            => 'No hay registros de asistencia hoy',
                                    'description'      => 'Usa el botón de arriba para marcar el ingreso o la salida de un empleado.',
                                    'actionLabel'      => 'Marcar Asistencia (Staff)',
                                    'actionUrl'        => '#',
                                    'actionTarget'     => 'modal-staff',
                                    'actionTargetType' => 'modal',
                                ])
                            </td>
                        </tr>
                    @endforelse
                    <tr data-search-empty hidden>
                        <td colspan="7" class="text-center text-muted py-4">No encontramos asistencias que coincidan.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>{{-- /tabla-staff --}}

{{-- ═══════════════════════════════════════════════════════════════════════════
     PANTALLA 3: TABLA — Empleados Temporales (oculta por defecto)
     ═══════════════════════════════════════════════════════════════════════════ --}}
<div id="tabla-temporales" class="d-none">

    <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
        <button type="button" class="view-back-btn" data-show-view="menu-hub">
            <i class="bi bi-arrow-left" aria-hidden="true"></i> Volver al menú
        </button>
        <h2 class="h5 mb-0 me-auto">Historial de empleados temporales</h2>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-temporal-marcacion" @disabled($empleadosTemporales->isEmpty())>
                <i class="bi bi-person-clock me-1" aria-hidden="true"></i>Marcar Asistencia
            </button>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-nuevo-temporal">
                <i class="bi bi-person-plus me-1" aria-hidden="true"></i>Nuevo Temporal
            </button>
        </div>
    </div>

    <div class="card data-table-card" data-live-region="temporary-employees">
        <div class="card-header list-card-header">
            <h2 class="h6 mb-0">Historial de empleados temporales</h2>
            <div class="list-search-wrap" data-list-search>
                <label class="visually-hidden" for="filter-temporary-employees">Buscar empleados temporales</label>
                <div class="list-search-field">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input id="filter-temporary-employees" type="search" placeholder="Buscar por nombre o fecha..." autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="suggestions-temporary-employees" data-list-search-input>
                </div>
                <ul class="list-search-suggestions" id="suggestions-temporary-employees" role="listbox" hidden></ul>
                <span class="list-search-count" data-search-count></span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover data-table align-middle">
                <thead class="data-table-head">
                    <tr>
                        <th>Empleado</th>
                        <th>Hora de ingreso</th>
                        <th>Hora de salida</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($empleadosTemporales as $empleadoTemporal)
                        <tr
                            data-search-row
                            data-search-value="{{ $empleadoTemporal->nombre_temp ?? $empleadoTemporal->empleado?->nombres ?? 'Empleado eliminado' }} {{ $empleadoTemporal->hora_ingreso }} {{ $empleadoTemporal->hora_salida }} {{ $empleadoTemporal->agencia_outsourcing }}"
                            data-suggestion-label="{{ $empleadoTemporal->nombre_temp ?? $empleadoTemporal->empleado?->nombres ?? 'Empleado eliminado' }}"
                        >
                            <td class="data-table-primary">{{ $empleadoTemporal->nombre_temp ?? $empleadoTemporal->empleado?->nombres ?? 'Empleado eliminado' }}</td>
                            <td class="text-nowrap data-table-number">{{ $empleadoTemporal->hora_ingreso ?? '—' }}</td>
                            <td class="text-nowrap data-table-number">{{ $empleadoTemporal->hora_salida ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="table-empty-cell">
                                @include('components.empty-state', [
                                    'title'            => 'No hay empleados temporales registrados',
                                    'description'      => 'Registra primero al empleado temporal para luego marcar su horario.',
                                    'actionLabel'      => 'Nuevo Emp. Temporal',
                                    'actionUrl'        => '#',
                                    'actionTarget'     => 'modal-nuevo-temporal',
                                    'actionTargetType' => 'modal',
                                ])
                            </td>
                        </tr>
                    @endforelse
                    <tr data-search-empty hidden>
                        <td colspan="3" class="text-center text-muted py-4">No encontramos empleados temporales que coincidan.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>{{-- /tabla-temporales --}}

{{-- ═══════════════════════════════════════════════════════════════════════════
     MODALES (los formularios viven aquí — sin cambios de backend)
     ═══════════════════════════════════════════════════════════════════════════ --}}

{{-- Modal 1: Marcar Asistencia (Staff) --}}
<div class="modal fade asistencia-modal" id="modal-staff" tabindex="-1"
     aria-labelledby="modal-staff-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="modal-title-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-person-check" aria-hidden="true"></i>
                    </span>
                    <h2 class="modal-title fs-6 fw-bold" id="modal-staff-title">Marcar asistencia — Personal Fijo</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="attendance-registration-form" action="{{ route('asistencia.ingreso') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="marcacion-empleado" class="form-label">Empleado</label>
                        <select name="id_empleado" id="marcacion-empleado" class="form-select" required>
                            <option value="">Seleccione un empleado</option>
                            @foreach ($empleados as $empleado)
                                <option value="{{ $empleado->id_empleado }}" @selected(old('id_empleado') == $empleado->id_empleado)>
                                    {{ $empleado->nombres }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
                        Elige el empleado y luego selecciona si es un ingreso o una salida.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" formaction="{{ route('asistencia.salida') }}" class="btn btn-outline-primary" @disabled($empleados->isEmpty())>
                        <i class="bi bi-box-arrow-left me-1" aria-hidden="true"></i>Registrar salida
                    </button>
                    <button type="submit" class="btn btn-success" @disabled($empleados->isEmpty())>
                        <i class="bi bi-box-arrow-in-right me-1" aria-hidden="true"></i>Registrar ingreso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal 2: Marcar Asistencia (Temporal) --}}
<div class="modal fade asistencia-modal" id="modal-temporal-marcacion" tabindex="-1"
     aria-labelledby="modal-temporal-marcacion-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="modal-title-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-person-clock" aria-hidden="true"></i>
                    </span>
                    <h2 class="modal-title fs-6 fw-bold" id="modal-temporal-marcacion-title">Marcar asistencia — Temporal</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form action="{{ route('asistencia.temporales.ingreso') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="marcacion-temporal" class="form-label">Empleado temporal</label>
                        <select name="id_temporal" id="marcacion-temporal" class="form-select" required>
                            <option value="">Seleccione un empleado temporal</option>
                            @foreach ($empleadosTemporales as $empleadoTemporal)
                                <option value="{{ $empleadoTemporal->id_temporal }}" @selected(old('id_temporal') == $empleadoTemporal->id_temporal)>
                                    {{ $empleadoTemporal->nombre_temp ?? $empleadoTemporal->empleado?->nombres ?? 'Empleado temporal' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if ($empleadosTemporales->isEmpty())
                        <div class="alert alert-warning py-2 px-3 small mb-0">
                            <i class="bi bi-exclamation-triangle me-1" aria-hidden="true"></i>
                            Primero registra un empleado temporal usando el botón "Nuevo Emp. Temporal".
                        </div>
                    @else
                        <p class="text-muted small mb-0">
                            <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
                            Elige el empleado temporal y selecciona si es un ingreso o una salida.
                        </p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" formaction="{{ route('asistencia.temporales.salida') }}" class="btn btn-outline-primary" @disabled($empleadosTemporales->isEmpty())>
                        <i class="bi bi-box-arrow-left me-1" aria-hidden="true"></i>Registrar salida
                    </button>
                    <button type="submit" class="btn btn-success" @disabled($empleadosTemporales->isEmpty())>
                        <i class="bi bi-box-arrow-in-right me-1" aria-hidden="true"></i>Registrar ingreso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal 3: Registrar Nuevo Empleado Temporal --}}
<div class="modal fade asistencia-modal" id="modal-nuevo-temporal" tabindex="-1"
     aria-labelledby="modal-nuevo-temporal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="modal-title-icon bg-secondary bg-opacity-10 text-secondary">
                        <i class="bi bi-person-plus" aria-hidden="true"></i>
                    </span>
                    <h2 class="modal-title fs-6 fw-bold" id="modal-nuevo-temporal-title">Registrar empleado temporal</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="temporary-registration-form" action="{{ route('asistencia.temporales.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-1">
                        <label for="temporal-empleado" class="form-label">Nombre del empleado</label>
                        <input
                            type="text"
                            name="nombre_temp"
                            id="temporal-empleado"
                            value="{{ old('nombre_temp') }}"
                            class="form-control"
                            maxlength="100"
                            placeholder="Escribe el nombre completo"
                            required
                            autofocus
                        >
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-person-plus me-1" aria-hidden="true"></i>Registrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    var VIEWS = ['menu-hub', 'tabla-staff', 'tabla-temporales'];

    window.showView = function showView(targetId) {
        VIEWS.forEach(function (id) {
            var el = document.getElementById(id);
            if (el) { el.classList.toggle('d-none', id !== targetId); }
        });
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-show-view]');
        if (btn) { showView(btn.dataset.showView); }
    });
}());
</script>
@endpush
