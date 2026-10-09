<?php

namespace App\Http\Controllers;

use App\Http\Requests\AsistenciaRequest;
use App\Models\Asistencia;
use App\Models\Empleado;
use App\Models\EmpleadoTemporal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AsistenciaController extends Controller
{
    public function index(): View
    {
        $empleados = Empleado::orderBy('nombres')->get();
        $asistencias = Asistencia::with('empleado')->orderByDesc('fecha')
            ->orderByRaw('hora_ingreso DESC NULLS LAST')->get();
        $empleadosTemporales = EmpleadoTemporal::with('empleado')->orderByDesc('id_temporal')->get();

        return view('asistencia.index', compact('empleados', 'asistencias', 'empleadosTemporales'));
    }

    public function inventario(Request $request): View
    {
        $data = $request->validate([
            'fecha' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $fecha = $data['fecha'] ?? now()->toDateString();

        $empleados = Empleado::orderBy('nombres')->get();
        $asistencias = Asistencia::whereDate('fecha', $fecha)
            ->orderByDesc('id_asistencia')->get()
            ->unique('id_empleado')->keyBy('id_empleado');
        $empleadosTemporales = EmpleadoTemporal::with('empleado')
            ->whereDate('fecha_inicio', $fecha)
            ->whereNotNull('hora_ingreso')
            ->orderBy('nombre_temp')->get();

        return view('asistencia.inventario', compact('asistencias', 'empleados', 'empleadosTemporales', 'fecha'));
    }

    public function registrarTemporal(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre_temp' => ['required', 'string', 'max:100'],
        ]);

        EmpleadoTemporal::create([
            'nombre_temp' => trim($data['nombre_temp']),
        ]);

        return to_route('asistencia.index')->with('mensaje', 'temporal_registrado');
    }

    public function store(AsistenciaRequest $request): RedirectResponse
    {
        $this->guardarAsistencia($request);

        return to_route('asistencia.index')->with('mensaje', 'registrado');
    }

    public function edit(Asistencia $asistencia): View
    {
        $empleados = Empleado::orderBy('nombres')->get();

        return view('asistencia.editar', compact('asistencia', 'empleados'));
    }

    public function update(AsistenciaRequest $request, Asistencia $asistencia): RedirectResponse
    {
        $this->guardarAsistencia($request, $asistencia);

        return to_route('asistencia.index')->with('mensaje', 'actualizado');
    }

    public function destroy(Asistencia $asistencia): RedirectResponse
    {
        $asistencia->delete();

        return to_route('asistencia.index')->with('mensaje', 'eliminado');
    }

    public function registrarIngreso(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_empleado' => ['required', 'integer', Rule::exists(Empleado::class, 'id_empleado')],
        ]);

        DB::transaction(function () use ($data): void {
            $empleado = Empleado::lockForUpdate()->findOrFail($data['id_empleado']);
            $ahora = now();
            $abierta = $empleado->asistencias()->whereDate('fecha', $ahora->toDateString())
                ->whereNotNull('hora_ingreso')->whereNull('hora_salida')->exists();

            if ($abierta) {
                throw ValidationException::withMessages(['id_empleado' => 'Este empleado ya tiene un ingreso abierto.']);
            }

            $empleado->asistencias()->create([
                'fecha' => $ahora->toDateString(),
                'hora_ingreso' => $ahora->format('H:i:s'),
            ]);
        });

        return to_route('asistencia.index');
    }

    public function registrarSalida(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_empleado' => ['required', 'integer', Rule::exists(Empleado::class, 'id_empleado')],
        ]);

        DB::transaction(function () use ($data): void {
            $empleado = Empleado::lockForUpdate()->findOrFail($data['id_empleado']);
            $ahora = now();
            $asistencia = $empleado->asistencias()->whereDate('fecha', $ahora->toDateString())
                ->whereNotNull('hora_ingreso')->whereNull('hora_salida')
                ->orderByDesc('id_asistencia')->lockForUpdate()->first();

            if (! $asistencia) {
                throw ValidationException::withMessages([
                    'id_empleado' => 'Este empleado no tiene un ingreso abierto para registrar la salida.',
                ]);
            }

            if ($asistencia->hora_ingreso > $ahora->format('H:i:s')) {
                throw ValidationException::withMessages(['id_empleado' => 'La salida no puede ser anterior al ingreso.']);
            }

            $asistencia->update(['hora_salida' => $ahora->format('H:i:s')]);
        });

        return to_route('asistencia.index');
    }

    public function registrarIngresoTemporal(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_temporal' => ['required', 'integer', Rule::exists(EmpleadoTemporal::class, 'id_temporal')],
        ]);

        DB::transaction(function () use ($data): void {
            $empleadoTemporal = EmpleadoTemporal::lockForUpdate()->findOrFail($data['id_temporal']);
            $ahora = now();

            if ($empleadoTemporal->hora_ingreso !== null) {
                throw ValidationException::withMessages([
                    'id_temporal' => 'Este empleado temporal ya tiene un ingreso registrado.',
                ]);
            }

            $empleadoTemporal->update([
                'fecha_inicio' => $ahora->toDateString(),
                'hora_ingreso' => $ahora->format('H:i:s'),
            ]);
        });

        return to_route('asistencia.index')->with('mensaje', 'temporal_ingreso');
    }

    public function registrarSalidaTemporal(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_temporal' => ['required', 'integer', Rule::exists(EmpleadoTemporal::class, 'id_temporal')],
        ]);

        DB::transaction(function () use ($data): void {
            $empleadoTemporal = EmpleadoTemporal::lockForUpdate()->findOrFail($data['id_temporal']);
            $ahora = now();

            if ($empleadoTemporal->hora_ingreso === null) {
                throw ValidationException::withMessages([
                    'id_temporal' => 'Este empleado temporal no tiene un ingreso abierto para registrar la salida.',
                ]);
            }

            if ($empleadoTemporal->hora_salida !== null) {
                throw ValidationException::withMessages([
                    'id_temporal' => 'Este empleado temporal ya tiene una salida registrada.',
                ]);
            }

            $empleadoTemporal->update(['hora_salida' => $ahora->format('H:i:s')]);
        });

        return to_route('asistencia.index')->with('mensaje', 'temporal_salida');
    }

    private function guardarAsistencia(AsistenciaRequest $request, ?Asistencia $asistencia = null): void
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $asistencia): void {
            Empleado::whereIn('id_empleado', array_filter([$data['id_empleado'], $asistencia?->id_empleado]))
                ->orderBy('id_empleado')->lockForUpdate()->get();

            $duplicada = Asistencia::where('id_empleado', $data['id_empleado'])
                ->whereDate('fecha', $data['fecha'])
                ->when($asistencia, fn (Builder $query): Builder => $query->where('id_asistencia', '!=', $asistencia->getKey()))
                ->exists();

            if ($duplicada) {
                throw ValidationException::withMessages(['fecha' => 'El empleado ya tiene asistencia registrada en esta fecha.']);
            }

            if ($asistencia) {
                $asistencia->update($data);
            } else {
                Asistencia::create($data);
            }
        });
    }
}
