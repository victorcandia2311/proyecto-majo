<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmpleadoRequest;
use App\Models\Empleado;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmpleadoController extends Controller
{
    public function index(): View
    {
        $empleados = Empleado::orderByDesc('id_empleado')->get();

        return view('empleados.index', compact('empleados'));
    }

    public function store(EmpleadoRequest $request): RedirectResponse
    {
        Empleado::create($request->validated());

        return to_route('empleados.index');
    }

    public function edit(Empleado $empleado): View
    {
        return view('empleados.editar', compact('empleado'));
    }

    public function update(EmpleadoRequest $request, Empleado $empleado): RedirectResponse
    {
        $empleado->update($request->validated());

        return to_route('empleados.index');
    }

    public function destroy(Empleado $empleado): RedirectResponse
    {
        $empleado->delete();

        return to_route('empleados.index');
    }
}
