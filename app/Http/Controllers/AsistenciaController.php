<?php

namespace App\Http\Controllers;

use App\Http\Requests\AsistenciaRequest;
use App\Models\Asistencia;
use App\Models\Empleado;
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

        return view('asistencia.index', compact('empleados', 'asistencias'));
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
